<?php

namespace Phoenix;

include '../src/crm_init.php';

$tt = '08:44:00';

echo roundTime( $tt ) . '<br>';
echo roundTime( $tt, 1 ) . '<br>';
echo roundTime( $tt, -1 ) . '<br>';