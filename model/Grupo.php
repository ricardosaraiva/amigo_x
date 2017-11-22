<?php
namespace Model;

class Grupo extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'grupo';

    public static function getPermissao($usuario, $grupo) {
        $permissao = self::join('grupo_usuario', 'grupo_usuario.id_grupo', '=', 'grupo.id')->
            where('grupo.id','=', (int) $grupo)->
            where('grupo.status','=', 1)->
            where('grupo_usuario.id_usuario','=', (int) $usuario)->
            select('grupo_usuario.permissao')->
        get();        

        if(!isset($permissao[0]->permissao)) {
            return false;
        }

        return $permissao[0]->permissao;
    }
}
