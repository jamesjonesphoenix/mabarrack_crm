<?php
foreach ( $options as $option ) {
    $selected = $option->id === $shift->worker->id ? ' selected="selected"' : '';
    echo '<option value="' . $option->id . '"' . $selected . '>' . $option->name . '</option>';
}