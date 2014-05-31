<?php

// -----------------------------------------------------------------------------
// basics.

// $max_num_modes can be null to get all of them (unlimited).
function stats_modes($nn, $max_num_modes)
{
        if (!$nn)
                return null;

        // count keys.
        $n2count = array();
        foreach ($nn as $n)
                map_incr($n2count, $n);

        // get max count.
        $max_count = null;
        foreach ($n2count as $n => $count)
                if ($max_count === null)
                        $max_count = $count;
                else
                        $max_count = max($max_count, $count);

        // get keys with count = max count.
        $rr = array();
        foreach ($n2count as $n => $count) {
                if ($count == $max_count)
                        $r[] = $n;
                if ($max_num_modes !== null && $max_num_modes <= count($rr))
                        break;
        }

        return $rr;
}

function stats_median($nn) {
        if (!$nn)
                return null;

        sort($nn);
        $len = count($nn);
        if (!($len % 2)) {
                $a = $len / 2;
                $b = ($len / 2) + 1;
                return ($nn[$a] + $nn[$b]) / 2;
        } else {
                return $nn[$len / 2];
        }
}

function stats_stddev($nn, $avg, $ddof=1)
{
        $len = count($nn);
        if ($len <= 1)
                return null;

        $var = 0.0;
        foreach ($nn as $n)
                $var += ($avg - $n) * ($avg - $n);
        $div = ($len - $ddof);
        if ($div === null)
                return null;

        $var /= $div;
        $stddev = sqrt($var);
        return $stddev;
}

// -----------------------------------------------------------------------------
// skewness.

// g_1 = m_3 / m_2^{3/2}
//
// "This is the typical definition used in many older textbooks."  (also matlab
// and wolfram alpha.)
function stats_skewness_type_1($nn, $avg)
{
        if (!$nn)
                return null;

        $m3 = 0.0;
        $m2 = 0.0;
        foreach ($nn as $n) {
                $m3 += pow(($n - $avg), 3);
                $m2 += pow(($n - $avg), 2);
        }
        if (!$m2)
                return null;

        $n = count($nn);
        $r = sqrt($n) * $m3 / (pow($m2, 1.5));
        return $r;
}

// G_1 = g_1 \sqrt{n(n-1)} / (n-2)
//
// "The adjusted Fisher-Pearson standardized moment coefficient is the version
// found in Excel and several statistical packages including Minitab, SAS and
// SPSS."  (also google spreadsheets.)
function stats_skewness_type_2($nn, $avg)
{
        $g1 = stats_skewness_type_1($nn, $avg);
        $n = count($nn);
        if ($g1 === null || $n < 3)
                return null;

        return $g1 * sqrt($n * ($n - 1)) / ($n - 2);
}

// b_1 = m_3 / s^3
//
// "Used in MINITAB and BMDP."  (also R default.)
function stats_skewness_type_3($nn, $avg)
{
        $r = stats_skewness_type_1($nn, $avg);
        if ($r === null)
                return null;

        $n = count($nn);
        $r *= pow((1 - 1 / $n), 1.5);
        return $r;
}

function stats_skewness($type, $nn, $avg)
{
        if ($type == 1)
                return stats_skewness_type_1($nn, $avg);
        else if ($type == 2)
                return stats_skewness_type_2($nn, $avg);
        else if ($type == 3)
                return stats_skewness_type_3($nn, $avg);
        else
                return null;
}

// -----------------------------------------------------------------------------
// kurtosis.

function stats_kurtosis_sub($nn, $avg)
{
        $m2 = 0.0;
        $m4 = 0.0;
        foreach ($nn as $n) {
                $m2 += pow(($n - $avg), 2);
                $m4 += pow(($n - $avg), 4);
        }
        if (!$m2)
                return null;

        $r = count($nn) * $m4 / ($m2 * $m2);
        return $r;
}

// g_2 = m_4 / m_2^2 - 3.
function stats_kurtosis_type_1($nn, $avg)
{
        $g2 = stats_kurtosis_sub($nn, $avg);
        if ($g2 === null)
                return null;

        return $g2 - 3;
}

// G_2 = ((n+1) g_2 + 6) * (n-1) / ((n-2)(n-3)).
function stats_kurtosis_type_2($nn, $avg)
{
        $len = count($nn);
        if ($len < 4)
                return null;  // need at least 4 observations.

        $g2 = stats_kurtosis_sub($nn, $avg);

        if ($g2 === null)
                return null;

        $n = count($nn);
        $r = (($n + 1) * ($g2 - 3) + 6) * ($n - 1) / (($n - 2) * ($n - 3));
        return $r;
}

// b_2 = m_4 / s^4 - 3 = (g_2 + 3) (1 - 1/n)^2 - 3.
function stats_kurtosis_type_3($nn, $avg)
{
        $n = count($nn);
        $g2 = stats_kurtosis_sub($nn, $avg);
        if ($g2 === null)
                return null;

        $r = $g2 * pow((1 - (1 / $n)), 2) - 3;
        return $r;
}

function stats_kurtosis($type, $nn, $avg)
{
        if ($type == 1)
                return stats_kurtosis_type_1($nn, $avg);
        else if ($type == 2)
                return stats_kurtosis_type_2($nn, $avg);
        else if ($type == 3)
                return stats_kurtosis_type_3($nn, $avg);
        else
                return null;
}

?>
