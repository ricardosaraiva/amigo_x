$('input').on('change', function () {
    $(this).removeClass('inputErroAjax');
});

function ajax(param) {
    preload();
    var ajax = {
        url: param.url,
        type : (param.type == undefined || param.type == '') ? 'POST' : param.type,
        data : param.data,
        success : function (data, textStatus, xhr) {
            preloadClose();
            param.success (data, xhr.status);
        },
        error : function (e) {
            preloadClose();
            var msg = 'Ocorreu um inesperado';
            
            if (e.responseJSON != '' || e.responseJSON != undefined) {
                msg = '';
                for (item in e.responseJSON) {
                    msg = msg + e.responseJSON[item] + '<br>';
                    $('#' + item).addClass('inputErroAjax').focus();
                }
            }

            modal(msg, 'erro');
        }
    };

    $.ajax(ajax);
}

function preload() {
    showModal('#modalPreload');
}

function preloadClose() {
    setTimeout(function () {
        $('#modalPreload').modal('hide');
    }, 500);
}

var modalRetorno = '';
function modal(msg, tipo, retorno) {
    
    setTimeout(function () {

        modalRetorno = '';
        if(retorno != undefined) {
            modalRetorno = {
                cancelar : retorno.cancelar,
                ok : retorno.ok
            };
        }
        

        $('#msgModalAlerta').remove();
        
        var conteudo = '';
        var bg = '';

        if(tipo == 'erro') {
            conteudo = '<div class="msg">'+ msg +'</div>'+
                '<button type="button" class="btn" data-dismiss="modal" autofocus>OK</button>';
            bg = 'bg-danger';
        } else if(tipo == 'confirm') {
            bg = 'bg-confirm';
            conteudo = '<div class="msg">'+ msg +'</div>'+
                '<div class="grupoButtons">'+
                    '<button type="button" class="btn btn-danger" onclick="modalRetornoOperacao(\'cancelar\')" autofocus>CANCELAR</button> ' +
                    '<button type="button" class="btn btn-success" onclick="modalRetornoOperacao(\'ok\')">OK</button>' +
                '</div>';
        } 

        var modal = '<div class="modal fade" id="msgModalAlerta">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content '+ bg +'">' +
                    '<div class="modal-body text-center">' +
                        conteudo +
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>';

        $('body').append(modal);
        showModal('#msgModalAlerta');
    }, 500);
}

function modalRetornoOperacao(funcao) {
    $('#msgModalAlerta').modal("hide");
    if(modalRetorno[funcao] != undefined) {
        modalRetorno[funcao]();
    }
}

function showModal(el, focus) {
    $(el).modal({backdrop: 'static', keyboard: false, show: true});
   
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('[autofocus]').focus();
    });
    
}

function retornoOperacaoRemover() {
    $('.retornoOperacao').remove();
}

function retornoOperacao(msg, tempo, cor) {
    retornoOperacaoRemover();
    var modal = '<div class="retornoOperacao" style="background:'+cor+'">'+
            msg +
        '</div>';

    tempo = tempo == undefined ? 5000 : tempo;
    setTimeout(function () {
        retornoOperacaoRemover();
    }, tempo);

    $('body').append(modal);
}

function retornoOperacaoSucesso(msg, tempo) {
    retornoOperacao(msg, tempo, '#449d44');
}

function retornoOperacaoErro(msg, tempo) {
    retornoOperacao(msg, tempo, '#c9302c');
}

function tr(el, tipo, tds, alinhar, attrs, adicionar) { 
    var alinhamento = {
        l : 'text-left',
        c : 'text-center',
        r : 'text-right',
    }

    if(tipo == 'novo') {
        var atributos = '';
        for ( attr in attrs) {
            atributos = atributos + attr + '="'+attrs[attr]+'" ';
        }

        var html = '<tr ' + atributos + '>';
        
        for ( td in tds ) {
            html = html + '<td class="' + alinhamento[alinhar[td]] + '">' + tds[td] + '</td>';
        }
        
        html = html + '</tr>';

        (adicionar == 'final') ? $(el).append(html) : $(el).prepend(html);
        return false;
    }

    for ( attr in attrs) {
        $(el).attr(attr, attrs[attr]);
    }

    for ( td in tds ) {
        $(el).children('td:eq(' + (parseInt(td)) + ')').html(tds[td]);
    }
}