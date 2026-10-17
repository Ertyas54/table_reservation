<?php
declare(strict_types=1);
/* ============================================================================
 *  КАЛЬКУЛЯТОР ДИНАМИКИ ЧС НА НЕФТЕПЕРЕРАБАТЫВАЮЩЕМ ЗАВОДЕ
 *  Модель: система из 15 ОДУ (K1..K15) + 4 управляющих параметра (R1..R4)
 *  Вид функций: f_i(x) = c_i * x   (линейный), либо c_i (const), либо
 *               c_i * x / (1+|x|)  (насыщение)
 *  Численное интегрирование: метод Рунге-Кутты 4-го порядка
 * ========================================================================== */

/* ----------------------------- 1. МЕТАДАННЫЕ ----------------------------- */

$K_META = [
    1  => ['Время испарения ХОВ с поверхности земли',                'ч'],
    2  => ['Время ликвидации последствий аварии',                    'ч'],
    3  => ['Площадь заражения в результате аварии',                  'км²'],
    4  => ['Время подхода первичного/вторичного облака к н.п.',      'ч'],
    5  => ['Потери от первичного облака',                            'чел.'],
    6  => ['Потери от вторичного облака',                            'чел.'],
    7  => ['Количество получивших амбулаторную помощь',              'чел.'],
    8  => ['Количество размещённых в стационаре и реанимации',       'чел.'],
    9  => ['Количество поражённой техники',                          'ед.'],
    10 => ['Количество объёмов и растворов для обеззараживания',     'т'],
    11 => ['Количество сил и средств для проведения АСР',            'ед.'],
    12 => ['Эффективность системы оповещения',                       '%'],
    13 => ['Количество людей в зоне поражения',                      'чел.'],
    14 => ['Количество спасателей в зоне поражения',                 'чел.'],
    15 => ['Развитость системы МЧС',                                 'балл'],
];

$R_META = [
    1 => 'Уровень финансирования системы МЧС города',
    2 => 'Степень экономического развития города',
    3 => 'Наличие прибыли у предприятия',
    4 => 'Доля современного оборудования на предприятии',
];

/* --------------------- 2. ПРЕДУСТАНОВКА (НПЗ) --------------------------- */

$DEFAULT_K = [
    1 => 6.0,      // время испарения, ч
    2 => 24.0,     // время ликвидации, ч
    3 => 1.5,      // площадь заражения, км²
    4 => 2.0,      // время подхода облака, ч
    5 => 12.0,     // потери от первичного облака, чел
    6 => 30.0,     // потери от вторичного облака, чел
    7 => 120.0,    // амбулаторная помощь, чел
    8 => 45.0,     // стационар/реанимация, чел
    9 => 8.0,      // поражённая техника, ед
    10 => 25.0,    // объёмы/растворы для обеззараживания, т
    11 => 60.0,    // силы и средства для АСР, ед
    12 => 70.0,    // эффективность оповещения, %
    13 => 1500.0,  // людей в зоне поражения, чел
    14 => 90.0,    // спасателей в зоне поражения, чел
    15 => 6.0,     // развитость системы МЧС, балл
];

$DEFAULT_R = [1 => 0.70, 2 => 0.60, 3 => 0.50, 4 => 0.40];

/* Коэффициенты c1..c55 (подобраны под предустановку НПЗ) */
$DEFAULT_C = [
    1=>0.012, 2=>0.012, 3=>0.012,
    4=>0.025, 5=>0.025, 6=>0.025, 7=>0.025, 8=>0.025,
    9=>0.030, 10=>0.030, 11=>0.030, 12=>0.030,
    13=>0.200, 14=>0.070,
    15=>0.020,
    16=>0.200,
    17=>0.033, 18=>0.033, 19=>0.033, 20=>0.033,
    21=>0.020, 22=>0.020, 23=>0.020, 24=>0.020,
    25=>0.018, 26=>0.018, 27=>0.018, 28=>0.018, 29=>0.018, 30=>0.018,
    31=>0.030, 32=>0.030,
    33=>0.023, 34=>0.023, 35=>0.023,
    36=>0.120, 37=>0.120, 38=>0.120,
    39=>0.015, 40=>0.015, 41=>0.015, 42=>0.650,
    43=>0.0045, 44=>0.0045, 45=>0.0045, 46=>0.270,
    47=>0.150, 48=>0.150,
    49=>0.0045, 50=>0.0045, 51=>0.0045,
    52=>0.013, 53=>0.013, 54=>0.013, 55=>0.013,
];

$COLORS = [
    1=>'#e6194B', 2=>'#3cb44b', 3=>'#4363d8', 4=>'#f58231', 5=>'#911eb4',
    6=>'#42d4f4', 7=>'#f032e6', 8=>'#808000', 9=>'#9A6324', 10=>'#469990',
    11=>'#000075', 12=>'#800000', 13=>'#c0392b', 14=>'#008080', 15=>'#555555',
];

/* ------------------------- 3. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ -------------------- */

function postFloat(string $key, float $default, ?float $min = null, ?float $max = null): float
{
    if (!isset($_POST[$key]) || !is_numeric($_POST[$key])) return $default;
    $v = (float)$_POST[$key];
    if (!is_finite($v)) return $default;
    if ($min !== null && $v < $min) $v = $min;
    if ($max !== null && $v > $max) $v = $max;
    return $v;
}

/** Векторное сложение: a + s*b  (ключи 1..15) */
function v_add(array $a, array $b, float $s): array
{
    $out = [];
    foreach ($a as $i => $v) {
        $out[$i] = $v + $s * ($b[$i] ?? 0.0);
    }
    return $out;
}

/** Правая часть системы ОДУ */
function derivatives(array $K, array $R, array $C, string $mode): array
{
    $f = static function (int $i, float $x) use ($C, $mode): float {
        $c = $C[$i] ?? 0.0;
        switch ($mode) {
            case 'const': return $c;
            case 'sat':   return $c * $x / (1.0 + abs($x));
            default:      return $c * $x;          // linear
        }
    };

    $d = [];
    $d[1]  = -($f(1,$K[10]) * $f(2,$K[11]) * $f(3,$K[14]));

    $d[2]  = $f(4,$K[3]) * $f(5,$K[7]) * $f(6,$K[8]) * $f(7,$K[9]) * $f(8,$K[13])
        - ($f(9,$K[10]) * $f(10,$K[11]) * $f(11,$K[14]) * $f(12,$K[15]) * $R[1]
            + $R[2] + $R[3] + $R[4]);

    $d[3]  = $f(13,$K[1]) - ($f(14,$K[15]) * $R[1] + $R[3] + $R[4]);

    $d[4]  = $f(15,$K[1]);

    $d[5]  = $f(16,$K[1]) * $R[2] - $R[1];

    $d[6]  = $R[2] - ($f(17,$K[4]) * $f(18,$K[11]) * $f(19,$K[12]) * $f(20,$K[14]) * $R[1]);

    $d[7]  = $f(21,$K[5]) * $f(22,$K[6]) * $f(23,$K[13]) * $f(24,$K[15]) * $R[1]
        + $R[2] + $R[3];

    $d[8]  = $f(25,$K[5]) * $f(26,$K[6]) * $f(27,$K[11]) * $f(28,$K[13])
        * $f(29,$K[14]) * $f(30,$K[15]) * $R[1] + $R[2] + $R[3];

    $d[9]  = $f(31,$K[3]) * $f(32,$K[13]) * $R[2]
        - ($f(33,$K[10]) * $f(34,$K[11]) * $f(35,$K[14]) * $R[1]);

    $d[10] = $f(36,$K[3]) * $f(37,$K[9]) * $f(38,$K[15]) * $R[1]
        + $R[2] + $R[3] + $R[4];

    $d[11] = $f(39,$K[3]) * $f(40,$K[13]) * $f(41,$K[14]) * $R[1]
        + $R[2] + $R[3] - ($f(42,$K[15]) * $R[4]);

    $d[12] = $f(43,$K[11]) * $f(44,$K[13]) * $f(45,$K[14]) * $R[1]
        + $R[2] + $R[3] - $f(46,$K[15]);

    $d[13] = $f(47,$K[2]) * $f(48,$K[3]) * $R[2];

    $d[14] = $f(49,$K[11]) * $f(50,$K[12]) * $f(51,$K[13]) * $R[1] + $R[2];

    $d[15] = $f(52,$K[2]) * $f(53,$K[3]) * $f(54,$K[13]) * $f(55,$K[14]) * $R[1] + $R[2];

    return $d;
}

/* --------------------------- 4. ЧТЕНИЕ ВВОДА ---------------------------- */

$mode       = $_POST['mode']       ?? 'linear';
$clamp      = isset($_POST['clamp']) ? (bool)$_POST['clamp'] : true;
$T          = postFloat('T', 24.0, 0.01, 100000.0);
$dt         = postFloat('dt', 0.01, 1.0e-6, 100.0);
$chartVars  = $_POST['chart'] ?? [1, 2, 3, 13];

if (!in_array($mode, ['linear', 'const', 'sat'], true)) $mode = 'linear';

$K0 = [];
for ($i = 1; $i <= 15; $i++) {
    $K0[$i] = postFloat("K$i", $DEFAULT_K[$i], 0.0, 1.0e9);
}
$R = [];
for ($i = 1; $i <= 4; $i++) {
    $R[$i] = postFloat("R$i", $DEFAULT_R[$i], 0.0, 100.0);
}
$C = [];
for ($i = 1; $i <= 55; $i++) {
    $C[$i] = postFloat("C$i", $DEFAULT_C[$i], 0.0, 1.0e6);
}

if (!is_array($chartVars)) $chartVars = [1, 2, 3, 13];
$chartVars = array_values(array_unique(array_filter(
    array_map('intval', $chartVars),
    static fn(int $v): bool => $v >= 1 && $v <= 15
)));
if (!$chartVars) $chartVars = [1, 2, 3, 13];

/* --------------------------- 5. ИНТЕГРИРОВАНИЕ -------------------------- */

$steps = (int)round($T / $dt);
if ($steps < 1) { $steps = 1; }
if ($steps > 300000) { $steps = 300000; $dt = $T / $steps; }

$K       = $K0;
$t       = 0.0;
$history = [];
$history[] = ['t' => 0.0] + $K;

$vmin = array_fill_keys(range(1, 15), INF);
$vmax = array_fill_keys(range(1, 15), -INF);
foreach ($K as $i => $v) { $vmin[$i] = $v; $vmax[$i] = $v; }

$blowUp = false;

for ($s = 0; $s < $steps; $s++) {

    $k1 = derivatives($K, $R, $C, $mode);
    $k2 = derivatives(v_add($K, $k1, $dt / 2), $R, $C, $mode);
    $k3 = derivatives(v_add($K, $k2, $dt / 2), $R, $C, $mode);
    $k4 = derivatives(v_add($K, $k3, $dt),     $R, $C, $mode);

    foreach ($K as $i => $v) {
        $nv = $v + $dt / 6.0 * ($k1[$i] + 2.0 * $k2[$i] + 2.0 * $k3[$i] + $k4[$i]);

        if (!is_finite($nv)) { $nv = $v; $blowUp = true; }
        if ($clamp && $nv < 0.0) { $nv = 0.0; }
        if (abs($nv) > 1.0e12) { $nv = $v; $blowUp = true; }

        $K[$i] = $nv;
        if ($nv < $vmin[$i]) $vmin[$i] = $nv;
        if ($nv > $vmax[$i]) $vmax[$i] = $nv;
    }

    $t += $dt;
    $history[] = ['t' => $t] + $K;
}

/* ------------------------------ 6. ГРАФИК ------------------------------- */

function renderChart(array $history, array $vars, array $meta, array $colors): string
{
    if (count($history) < 2) return '<p class="muted">Недостаточно данных для графика.</p>';

    // Прореживание истории для отрисовки
    $maxPts = 500;
    $n = count($history);
    $step = max(1, (int)ceil($n / $maxPts));
    $series = [];
    for ($i = 0; $i < $n; $i += $step) $series[] = $history[$i];
    if (end($series) !== $history[$n - 1]) $series[] = $history[$n - 1];

    $W = 980; $H = 440; $ml = 78; $mr = 210; $mt = 24; $mb = 52;
    $pw = $W - $ml - $mr;
    $ph = $H - $mt - $mb;

    $tMax = $history[$n - 1]['t'];
    if ($tMax <= 0) $tMax = 1.0;

    $vmin = INF; $vmax = -INF;
    foreach ($series as $row) {
        foreach ($vars as $v) {
            $x = (float)$row[$v];
            if ($x < $vmin) $vmin = $x;
            if ($x > $vmax) $vmax = $x;
        }
    }
    if (!is_finite($vmin) || !is_finite($vmax)) { $vmin = 0.0; $vmax = 1.0; }
    if ($vmax - $vmin < 1e-12) { $vmax = $vmin + 1.0; }
    $pad = ($vmax - $vmin) * 0.06;
    $vmin -= $pad; $vmax += $pad;

    $X = static fn(float $tt): float => $ml + ($tt / $tMax) * $pw;
    $Y = static fn(float $yy): float => $mt + $ph - (($yy - $vmin) / ($vmax - $vmin)) * $ph;

    $svg  = '<svg viewBox="0 0 ' . $W . ' ' . $H . '" class="chart" xmlns="http://www.w3.org/2000/svg">';
    $svg .= '<rect x="0" y="0" width="' . $W . '" height="' . $H . '" fill="#ffffff"/>';

    // Горизонтальная сетка + подписи по Y
    for ($g = 0; $g <= 5; $g++) {
        $yy = $vmin + ($vmax - $vmin) * $g / 5;
        $py = $Y($yy);
        $svg .= sprintf('<line x1="%d" y1="%.2f" x2="%d" y2="%.2f" stroke="#e6e9ef" stroke-width="1"/>',
            $ml, $py, $ml + $pw, $py);
        $svg .= sprintf('<text x="%d" y="%.2f" text-anchor="end" font-size="11" fill="#667085">%s</text>',
            $ml - 8, $py + 4, htmlspecialchars(formatNum($yy), ENT_QUOTES));
    }

    // Вертикальная сетка + подписи по X
    for ($g = 0; $g <= 6; $g++) {
        $tt = $tMax * $g / 6;
        $px = $X($tt);
        $svg .= sprintf('<line x1="%.2f" y1="%d" x2="%.2f" y2="%d" stroke="#e6e9ef" stroke-width="1"/>',
            $px, $mt, $px, $mt + $ph);
        $svg .= sprintf('<text x="%.2f" y="%d" text-anchor="middle" font-size="11" fill="#667085">%s</text>',
            $px, $mt + $ph + 20, htmlspecialchars(formatNum($tt), ENT_QUOTES));
    }

    // Оси
    $svg .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#98a2b3" stroke-width="1.5"/>',
        $ml, $mt, $ml, $mt + $ph);
    $svg .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#98a2b3" stroke-width="1.5"/>',
        $ml, $mt + $ph, $ml + $pw, $mt + $ph);

    // Подписи осей
    $svg .= sprintf('<text x="%d" y="%d" text-anchor="middle" font-size="12" fill="#475467">t, ч</text>',
        $ml + (int)($pw / 2), $H - 10);

    // Кривые
    foreach ($vars as $v) {
        $pts = [];
        foreach ($series as $row) {
            $pts[] = sprintf('%.2f,%.2f', $X((float)$row['t']), $Y((float)$row[$v]));
        }
        $svg .= sprintf('<polyline fill="none" stroke="%s" stroke-width="2" stroke-linejoin="round" points="%s"/>',
            $colors[$v] ?? '#333', implode(' ', $pts));
    }

    // Легенда
    $ly = $mt + 8;
    foreach ($vars as $v) {
        $svg .= sprintf('<rect x="%d" y="%d" width="14" height="14" rx="3" fill="%s"/>',
            $ml + $pw + 18, $ly, $colors[$v] ?? '#333');
        $svg .= sprintf('<text x="%d" y="%d" font-size="12" fill="#344054">K%d — %s</text>',
            $ml + $pw + 38, $ly + 12, $v,
            htmlspecialchars(mb_substr($meta[$v][0] ?? '', 0, 26), ENT_QUOTES));
        $ly += 24;
        if ($ly > $mt + $ph - 6) break;
    }

    $svg .= '</svg>';
    return $svg;
}

function formatNum(float $v): string
{
    $a = abs($v);
    if ($a == 0.0) return '0';
    if ($a >= 1.0e5 || $a < 1.0e-3) return sprintf('%.2e', $v);
    if ($a >= 100) return sprintf('%.0f', $v);
    if ($a >= 1)   return sprintf('%.2f', $v);
    return sprintf('%.4f', $v);
}

/* ------------------------------ 7. ВЫВОД -------------------------------- */

$hasResult = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Калькулятор ЧС — Нефтеперерабатывающий завод</title>
    <style>
        :root{
            --bg:#f5f7fb; --card:#ffffff; --line:#e4e7ec; --txt:#101828;
            --muted:#667085; --acc:#1d4ed8; --acc2:#0f766e; --danger:#b42318;
        }
        *{box-sizing:border-box}
        body{
            margin:0; padding:24px 16px 64px;
            font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif;
            background:var(--bg); color:var(--txt); font-size:14px; line-height:1.45;
        }
        .wrap{max-width:1240px;margin:0 auto}
        h1{font-size:23px;margin:0 0 6px}
        h2{font-size:17px;margin:0 0 14px;padding-bottom:8px;border-bottom:2px solid var(--line)}
        h3{font-size:14px;margin:18px 0 10px;color:var(--acc2);text-transform:uppercase;letter-spacing:.4px}
        .sub{color:var(--muted);margin:0 0 22px;font-size:13px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:12px;
            padding:20px;margin-bottom:20px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px}
        .grid.coef{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px}
        label{display:block;font-size:12px;color:var(--muted);margin-bottom:4px}
        label b{color:var(--txt);font-weight:600}
        input[type=number],select{
            width:100%;padding:7px 9px;border:1px solid #d0d5dd;border-radius:7px;
            font-size:13px;font-family:inherit;background:#fff;color:var(--txt);
        }
        input[type=number]:focus,select:focus{outline:none;border-color:var(--acc);
            box-shadow:0 0 0 3px rgba(29,78,216,.12)}
        .unit{font-size:11px;color:#98a2b3;margin-left:4px}
        button{
            background:var(--acc);color:#fff;border:0;border-radius:8px;
            padding:11px 26px;font-size:14px;font-weight:600;cursor:pointer;
            font-family:inherit;transition:.15s;
        }
        button:hover{background:#1e40af}
        button.sec{background:#fff;color:var(--acc);border:1px solid #c7d2fe;font-weight:500;padding:11px 18px}
        button.sec:hover{background:#eef2ff}
        .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
        table{width:100%;border-collapse:collapse;font-size:13px}
        th,td{padding:8px 10px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
        th{background:#f9fafb;font-weight:600;font-size:12px;color:#475467;
            text-transform:uppercase;letter-spacing:.3px;position:sticky;top:0}
        td.num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
        tr:hover td{background:#fbfcfe}
        .pos{color:#027a48;font-weight:600}
        .neg{color:var(--danger);font-weight:600}
        .muted{color:var(--muted)}
        .chart{width:100%;height:auto;display:block;border:1px solid var(--line);border-radius:8px;background:#fff}
        .chk{display:inline-flex;align-items:center;gap:6px;margin:3px 12px 3px 0;font-size:12px;color:#344054;cursor:pointer}
        .chk input{accent-color:var(--acc)}
        details{border:1px solid var(--line);border-radius:9px;padding:0 14px;background:#fcfcfd}
        details[open]{padding-bottom:14px}
        summary{cursor:pointer;padding:12px 0;font-weight:600;color:var(--acc2);font-size:13px;
            text-transform:uppercase;letter-spacing:.4px;list-style:none}
        summary::-webkit-details-marker{display:none}
        summary::before{content:"▸ ";font-size:12px}
        details[open] summary::before{content:"▾ "}
        .warn{background:#fffaeb;border:1px solid #fedf89;color:#93370d;
            border-radius:9px;padding:11px 14px;margin-bottom:16px;font-size:13px}
        .ok{background:#ecfdf3;border:1px solid #a6f4c5;color:#054f31;
            border-radius:9px;padding:11px 14px;margin-bottom:16px;font-size:13px}
        .badge{display:inline-block;background:#eef2ff;color:#3730a3;border-radius:6px;
            padding:1px 7px;font-size:11px;font-weight:600;margin-right:6px}
        .scroll{max-height:560px;overflow:auto;border-radius:8px}
        .row{display:flex;gap:26px;flex-wrap:wrap;margin-bottom:16px}
        .kpi{flex:1;min-width:170px;background:#f9fafb;border:1px solid var(--line);
            border-radius:10px;padding:13px 15px}
        .kpi .t{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px}
        .kpi .v{font-size:21px;font-weight:700;margin-top:3px;font-variant-numeric:tabular-nums}
        .kpi .u{font-size:11px;color:#98a2b3}
    </style>
</head>
<body>
<div class="wrap">

    <h1>Калькулятор динамики чрезвычайной ситуации</h1>
    <p class="sub">Объект: <b>нефтеперерабатывающий завод</b>. Модель — система из 15 дифференциальных
        уравнений (K<sub>1</sub>…K<sub>15</sub>) с управляющими параметрами R<sub>1</sub>…R<sub>4</sub>.
        Интегрирование: метод Рунге–Кутты 4-го порядка.</p>

    <form method="post" action="">

        <!-- ================= НАЧАЛЬНЫЕ УСЛОВИЯ ================= -->
        <div class="card">
            <h2>1. Начальные условия K<sub>i</sub>(0)</h2>
            <div class="grid">
                <?php for ($i = 1; $i <= 15; $i++): ?>
                    <div>
                        <label for="K<?= $i ?>"><b>K<?= $i ?></b> — <?= htmlspecialchars($K_META[$i][0], ENT_QUOTES) ?>
                            <span class="unit">[<?= htmlspecialchars($K_META[$i][1], ENT_QUOTES) ?>]</span></label>
                        <input type="number" step="any" min="0" id="K<?= $i ?>" name="K<?= $i ?>"
                               value="<?= htmlspecialchars((string)$K0[$i], ENT_QUOTES) ?>">
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- ================= ПАРАМЕТРЫ УПРАВЛЕНИЯ ================= -->
        <div class="card">
            <h2>2. Управляющие параметры R<sub>j</sub></h2>
            <div class="grid">
                <?php for ($j = 1; $j <= 4; $j++): ?>
                    <div>
                        <label for="R<?= $j ?>"><b>R<?= $j ?></b> — <?= htmlspecialchars($R_META[$j], ENT_QUOTES) ?></label>
                        <input type="number" step="any" min="0" id="R<?= $j ?>" name="R<?= $j ?>"
                               value="<?= htmlspecialchars((string)$R[$j], ENT_QUOTES) ?>">
                    </div>
                <?php endfor; ?>
            </div>
            <p class="muted" style="margin:12px 0 0;font-size:12px">
                Рекомендуемый диапазон: 0…1 (доли/нормированные баллы). R<sub>1</sub> — финансирование МЧС,
                R<sub>2</sub> — экономическое развитие города, R<sub>3</sub> — прибыль предприятия,
                R<sub>4</sub> — доля современного оборудования.
            </p>
        </div>

        <!-- ================= ПАРАМЕТРЫ РАСЧЁТА ================= -->
        <div class="card">
            <h2>3. Параметры расчёта</h2>
            <div class="grid">
                <div>
                    <label for="T"><b>Горизонт моделирования T</b> <span class="unit">[ч]</span></label>
                    <input type="number" step="any" min="0.01" id="T" name="T"
                           value="<?= htmlspecialchars((string)$T, ENT_QUOTES) ?>">
                </div>
                <div>
                    <label for="dt"><b>Шаг интегрирования dt</b> <span class="unit">[ч]</span></label>
                    <input type="number" step="any" min="0.000001" id="dt" name="dt"
                           value="<?= htmlspecialchars((string)$dt, ENT_QUOTES) ?>">
                </div>
                <div>
                    <label for="mode"><b>Вид функций f<sub>i</sub>(x)</b></label>
                    <select id="mode" name="mode">
                        <option value="linear" <?= $mode === 'linear' ? 'selected' : '' ?>>Линейная: c·x</option>
                        <option value="sat"    <?= $mode === 'sat'    ? 'selected' : '' ?>>Насыщение: c·x/(1+|x|)</option>
                        <option value="const"  <?= $mode === 'const'  ? 'selected' : '' ?>>Константа: c</option>
                    </select>
                </div>
                <div>
                    <label><b>Ограничения</b></label>
                    <label class="chk" style="margin-top:6px">
                        <input type="checkbox" name="clamp" value="1" <?= $clamp ? 'checked' : '' ?>>
                        Не допускать отрицательных значений K<sub>i</sub>
                    </label>
                </div>
            </div>

            <h3>Переменные на графике</h3>
            <div>
                <?php for ($i = 1; $i <= 15; $i++): ?>
                    <label class="chk">
                        <input type="checkbox" name="chart[]" value="<?= $i ?>"
                            <?= in_array($i, $chartVars, true) ? 'checked' : '' ?>>
                        K<?= $i ?>
                    </label>
                <?php endfor; ?>
            </div>

            <!-- ---------- Коэффициенты ---------- -->
            <div style="margin-top:20px">
                <details>
                    <summary>Коэффициенты c<sub>1</sub>…c<sub>55</sub> (тонкая настройка модели)</summary>
                    <p class="muted" style="font-size:12px;margin:0 0 12px">
                        Каждая функция f<sub>i</sub> имеет свой коэффициент c<sub>i</sub>.
                        Значения по умолчанию откалиброваны под предустановку «Нефтеперерабатывающий завод».
                    </p>
                    <div class="grid coef">
                        <?php for ($i = 1; $i <= 55; $i++): ?>
                            <div>
                                <label for="C<?= $i ?>">c<sub><?= $i ?></sub></label>
                                <input type="number" step="any" min="0" id="C<?= $i ?>" name="C<?= $i ?>"
                                       value="<?= htmlspecialchars((string)$C[$i], ENT_QUOTES) ?>">
                            </div>
                        <?php endfor; ?>
                    </div>
                </details>
            </div>

            <div class="actions">
                <button type="submit">Рассчитать</button>
                <button type="reset" class="sec">Сбросить форму</button>
                <button type="button" class="sec" onclick="location.href=location.pathname">Вернуть предустановку НПЗ</button>
            </div>
        </div>
    </form>

    <?php if ($hasResult): ?>
        <!-- ================= РЕЗУЛЬТАТЫ ================= -->
        <div class="card">
            <h2>4. Результаты расчёта</h2>

            <?php if ($blowUp): ?>
                <div class="warn">
                    <b>Внимание.</b> Обнаружены числовые аномалии (переполнение / расходимость).
                    Уменьшите шаг <code>dt</code>, сократите горизонт <code>T</code> или
                    уменьшите коэффициенты c<sub>i</sub>.
                </div>
            <?php else: ?>
                <div class="ok">
                    Расчёт выполнен успешно: <b><?= (int)$steps ?></b> шагов интегрирования,
                    шаг <b>dt = <?= htmlspecialchars(formatNum($dt), ENT_QUOTES) ?></b> ч,
                    горизонт <b>T = <?= htmlspecialchars(formatNum($T), ENT_QUOTES) ?></b> ч.
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="kpi">
                    <div class="t">Площадь заражения (K<sub>3</sub>)</div>
                    <div class="v"><?= htmlspecialchars(formatNum($K[3]), ENT_QUOTES) ?></div>
                    <div class="u">км²</div>
                </div>
                <div class="kpi">
                    <div class="t">Людей в зоне поражения (K<sub>13</sub>)</div>
                    <div class="v"><?= htmlspecialchars(formatNum($K[13]), ENT_QUOTES) ?></div>
                    <div class="u">чел.</div>
                </div>
                <div class="kpi">
                    <div class="t">Суммарные потери (K<sub>5</sub>+K<sub>6</sub>)</div>
                    <div class="v"><?= htmlspecialchars(formatNum($K[5] + $K[6]), ENT_QUOTES) ?></div>
                    <div class="u">чел.</div>
                </div>
                <div class="kpi">
                    <div class="t">Время ликвидации (K<sub>2</sub>)</div>
                    <div class="v"><?= htmlspecialchars(formatNum($K[2]), ENT_QUOTES) ?></div>
                    <div class="u">ч</div>
                </div>
                <div class="kpi">
                    <div class="t">Силы и средства (K<sub>11</sub>)</div>
                    <div class="v"><?= htmlspecialchars(formatNum($K[11]), ENT_QUOTES) ?></div>
                    <div class="u">ед.</div>
                </div>
            </div>

            <div class="scroll">
                <table>
                    <thead>
                    <tr>
                        <th style="width:52px">Перем.</th>
                        <th>Показатель</th>
                        <th style="width:70px">Ед.</th>
                        <th class="num" style="width:110px">Начало</th>
                        <th class="num" style="width:110px">Конец</th>
                        <th class="num" style="width:120px">Изменение</th>
                        <th class="num" style="width:110px">Минимум</th>
                        <th class="num" style="width:110px">Максимум</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 1; $i <= 15; $i++):
                        $a = $K0[$i];
                        $b = $K[$i];
                        $delta = $b - $a;
                        $cls = $delta > 1e-12 ? 'pos' : ($delta < -1e-12 ? 'neg' : 'muted');
                        $sign = $delta > 1e-12 ? '+' : '';
                        ?>
                        <tr>
                            <td><span class="badge">K<?= $i ?></span></td>
                            <td><?= htmlspecialchars($K_META[$i][0], ENT_QUOTES) ?></td>
                            <td class="muted"><?= htmlspecialchars($K_META[$i][1], ENT_QUOTES) ?></td>
                            <td class="num"><?= htmlspecialchars(formatNum($a), ENT_QUOTES) ?></td>
                            <td class="num"><b><?= htmlspecialchars(formatNum($b), ENT_QUOTES) ?></b></td>
                            <td class="num <?= $cls ?>"><?= $sign . htmlspecialchars(formatNum($delta), ENT_QUOTES) ?></td>
                            <td class="num muted"><?= htmlspecialchars(formatNum($vmin[$i]), ENT_QUOTES) ?></td>
                            <td class="num muted"><?= htmlspecialchars(formatNum($vmax[$i]), ENT_QUOTES) ?></td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= ГРАФИК ================= -->
        <div class="card">
            <h2>5. Динамика показателей во времени</h2>
            <?= renderChart($history, $chartVars, $K_META, $COLORS) ?>
            <p class="muted" style="font-size:12px;margin:12px 0 0">
                Отображаются переменные: <?php
                $names = [];
                foreach ($chartVars as $v) $names[] = 'K' . $v;
                echo htmlspecialchars(implode(', ', $names), ENT_QUOTES);
                ?>. Изменить набор можно в разделе «Параметры расчёта».
            </p>
        </div>
    <?php else: ?>
        <div class="card">
            <p class="muted" style="margin:0">
                Заполните исходные данные и нажмите <b>«Рассчитать»</b>.
                По умолчанию загружена предустановка для нефтеперерабатывающего завода.
            </p>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
