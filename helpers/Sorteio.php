<?php

namespace Helpers;

class Sorteio {
	public static function sortear ($participantes) {

		//valida se tem mais de 3 participantes para relealizar um sorteio
		if(count($participantes) < 3) {
			throw new \Exception("É necessario ter no mínimo 3 participantes!");	
		}

		//mistura os elementos do array
		if(!shuffle($participantes)) {
			throw new \Exception("Ocorreu um erro inesperado!");
		}

		//gera os participantes
		$sorteio = [];
		foreach($participantes as $key => $participante) {
		    $amigo = isset($participantes[($key + 1)]) ? $participantes[($key + 1)] : $participantes[0];
		    $sorteio[$participante->id] = $amigo;
		}

		return $sorteio;
	}
}