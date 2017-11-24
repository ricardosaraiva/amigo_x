<?php

namespace Helpers;

class Formatar {
    public static function dataDb ($data) {
        return preg_replace('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})(.*)/', '$1-$2-$3$4', $data);
    }
}