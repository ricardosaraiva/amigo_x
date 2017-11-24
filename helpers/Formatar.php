<?php

namespace Helpers;

class Formatar {
    public static function dataDb ($data) {
        return preg_replace('/([0-9]{2})\/([0-9]{2})\/([0-9]{4})(.*)/', '$3-$2-$1$4', $data);
    }
}