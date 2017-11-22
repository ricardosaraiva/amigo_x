function cadastroGrupo(nome, editar) {
    $('#nome').val((nome == undefined ? '' : nome));
    $('#editarGrupo').val((editar == undefined ? 0 : editar));
    
    $('#cadastroGrupo .modal-title').html( (nome == undefined ) ? 'Novo grupo' : 'Editando grupo: ' + editar);
    showModal('#cadastroGrupo');
}

$('#cadastroGrupo').submit(function (e) {
    e.preventDefault();

    var url = ($('#editarGrupo').val() == '0') ? 'add': 'edit';
    var data = (url == 'edit') ? 
        {nome: $('#nome').val(), id:  $('#editarGrupo').val()} : {nome: $('#nome').val()};
    
    ajax({
        url: '/grupo/' + url,
        type: 'post',
        data: data,
        success: function (json) {
            $('#cadastroGrupo').modal("hide");
            var remover = (json.permissao == 'dono') ? 
                '<button class="btn btn-danger" onclick="remover('+json.codigo+')"><a class="fa fa-times"></a></button>' : '';
            
            var el = (url == 'add') ? '#grupos' : '#grupo' + data.id;
            var funcao = (url == 'add' ? 'novo' : 'editar');
            
            tr(el, funcao, [
                json.codigo,
                '<a href="/grupo/'+json.coigo+'">'+ json.nome + '</a>',
                '<button class="btn btn-primary"  onclick="cadastroGrupo(\'' + json.nome + '\', \'' + json.codigo + '\')"><a class="fa fa-pencil"></a></button> ' +
                remover
            ], ['l','l','l'], {id: 'grupo' + json.codigo});

            retornoOperacaoSucesso('Registro cadastrado com sucesso!');
        }
    })
})

function remover(id) {
    modal('Tem certeza que deseja remover este item?', 'confirm', {
        ok: function () {
            ajax({
                url: '/grupo/del',
                type: 'post',
                data: {id: id},
                success: function (json) {
                    $('#grupo' + id).remove();     
                    retornoOperacaoSucesso('Registro removido com sucesso!');
                }
            });
        }
    })
}

function sair(id) {
    modal('Tem certeza que deseja sair deste grupo?', 'confirm', {
        ok: function () {
            ajax({
                url: '/grupo/cancelar',
                type: 'post',
                data: {id: id},
                success: function (json) {
                    document.location.href = '/grupo';
                }
            });
        }
    })    
}

var options = {

    url: function(phrase) {
        return '/grupo/participantes/filtro';
    },

    getValue: function(json) {
        return json.nome + ' (' + json.email + ')';
    },

    ajaxSettings: {
        dataType: "json",
        method: "GET",
        data: {}
    },

    preparePostData: function(data) {
        data.filtro = $("#filtroNome").val();
        data.grupo = $("#grupoId").val();
        return data;
    },

    list: {
        maxNumberOfElements: 10,
        onChooseEvent: function() {
            var dados = $("#filtroNome").getSelectedItemData();

            modal('Tem certeza que deseja adicionar: ' 
                    + dados.nome + ' (' + dados.email + ')', 'confirm', {
                ok: function () {
                        
                    ajax({
                        url: '/grupo/convidar',
                        type: 'post',
                        data: {usuario: dados.id, grupo: $("#grupoId").val()},
                        success: function (json) {
                            $("#filtroNome").val('');
                            retornoOperacaoSucesso('Usuário convidado para grupo!');
                            tr('#participantes', 'novo', [
                                dados.nome + '(Convidado)',
                                dados.email,                                
                                'participante',                                
                                '<button class="btn btn-primary" onclick="editarParticipante('+dados.id+')">'+
                                '<i class="fa fa-pencil"></i></button>'
                            ], ['l','l','c','c'], {
                                class: 'aguardando',
                                id: 'tr' + dados.id
                            });
                        
                        }
                    });
                    
                }
            })
        }
    },

    requestDelay: 400
};

$("#filtroNome").easyAutocomplete(options);

function editarParticipante(id) {
    var participante = $('#tr' + id);
    var nome = participante.children('td:eq(0)').html();
    var email = participante.children('td:eq(1)').html();
    var permissao = participante.children('td:eq(2)').html();

    $('#codigo').val(id);
    $('#nome').val(nome.replace('(Convidado)', ''));
    $('#email').val(email);
    $('#permissao option[value="'+permissao+'"]').prop('selected', true);

    showModal('#modalParticipante');
}

function removerParticipante() {
    var grupo = $('#grupoId').val();
    var usuario = $('#codigo').val();
    modal('Tem certeza que deseja remover este participante?', 'confirm', {
        ok: function () {
            ajax({
                url: '/grupo/participantes/remover',
                type: 'post',
                data: {grupo: grupo, usuario: usuario},
                success: function (json) {
                    $('#tr' + usuario).remove();
                    retornoOperacaoSucesso('Participante removido com sucesso!');
                    $('#modalParticipante').modal('hide');
                }
            });    
        }
    }) 
}

function permissaoParticipante() {
    var grupo = $('#grupoId').val();
    var permissao = $('#permissao').val();
    var usuario = $('#codigo').val();

    ajax({
        url: '/grupo/participantes/permissao',
        type: 'post',
        data: {grupo: grupo, usuario: usuario, permissao: permissao},
        success: function (json) {
            $('#tr' + usuario).children('td:eq(2)').html(permissao);
            $('#modalParticipante').modal('hide');
            retornoOperacaoSucesso(json);    
        }
    });

    
}