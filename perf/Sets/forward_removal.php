<?php

require __DIR__ . '/../../vendor/autoload.php';

$avl = new \Ardent\AvlTree();
$splay = new \Ardent\SplayTree();
$numeric = new \Ardent\SortedNumericSet();
$array = [];

$start = microtime(TRUE);
$stop = microtime(TRUE);
$max = 10000;

$a = [];
for ($i = 0; $i < $max; $i++) {
    $a[] = $rand = mt_rand();
    $avl->add($rand);
    $splay->add($rand);
    $numeric->add($rand);
    $array[] = $rand;
}
$array = array_unique($array, SORT_NUMERIC);
sort($array, SORT_NUMERIC);

$start = microtime(TRUE);
for ($i = 0; $i < $max; $i++) {
    $avl->remove($a[$i]);
}
$stop = microtime(TRUE);
printf("AvlSet:   \t%d random removals took %fs.\n", $max, $stop - $start);

$start = microtime(TRUE);
for ($i = 0; $i < $max; $i++) {
    $splay->remove($a[$i]);
}
$stop = microtime(TRUE);
printf("SplaySet:\t%d random removals took %fs.\n", $max, $stop - $start);

$start = microtime(TRUE);
for ($i = 0; $i < $max; $i++) {
    $numeric->remove($a[$i]);
}
$stop = microtime(TRUE);
printf("NumericSet:\t%d random removals took %fs.\n", $max, $stop - $start);

$start = microtime(TRUE);
for ($i = 0; $i < $max; $i++) {
    $index = array_search($a[$i], $array, $strict = TRUE);
    unset($array[$index]);
}
$stop = microtime(TRUE);
printf("Array:   \t%d random removals took %fs.\n", $max, $stop - $start);
