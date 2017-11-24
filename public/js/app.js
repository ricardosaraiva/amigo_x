$('input').on('change', function () {
    $(this).removeClass('inputErroAjax');
});

$(".table tbody").delegate('tr.link button, tr.link a', 'click', function(e) {
    e.preventDefault();
    e.stopPropagation();
});

$(".table tbody").delegate('tr.link', 'click', function() {
    window.location = $(this).data("href");
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

function moeda(valor, casas) {
    valor = ( isNaN(valor) ) ? 0.00 : valor;

    /*formatando as casa decimais*/
    valor = parseFloat(valor);
    valor = valor.toFixed(casas);

    valor = valor.replace('.', ',');
    valor = valor.replace(/^((-{1})?[0-9]{1,3})([0-9]{3}[,]{1}[0-9]*)$/, '$1.$3');
    /*numero com ate 6 digitos depois da casa decimal*/
    valor = valor.replace(/^((-{1})?[0-9]{1,3})([0-9]{3})([0-9]{3}[,]{1}[0-9]*)$/, '$1.$3.$4');
    /*numero com ate 9 digitos depois da casa decimal*/
    valor = valor.replace(/^((-{1})?[0-9]{1,3})([0-9]{3})([0-9]{3})([0-9]{3}[,]{1}[0-9]*)$/, '$1.$3.$4.$5');
    /*numero com ate 12 digitos depois da casa decimal*/

    return valor;
}


//SMS
function resizeMsg() {
    var h = window.innerHeight
        || document.documentElement.clientHeight
        || document.body.clientHeight;

    $("#mensagens").css('height', (h - 270));
}

$(document).ready(function () {
    resizeMsg();

    $( window ).resize(function() {
        resizeMsg();
    });
})

function chat() {
    ajax({
        url: '/mensagem/chat',
        type: 'get',
        success: function (json) {
            $('#chatSessao').html('<option value="0">SELECIONAR CONVERSA</option>');

            for (i in  json ) {
                $('#chatSessao').append('<option value="'+json[i].id+'">' + json[i].descricao + '</option>');
            }

            $('#chat').fadeIn();
        }
    })
}

function chatFechar() {
    $('#mensagens').html('');
    $('#chat').fadeOut();
    chatCancelar = true;
}

var chatMsgId = 0;
var chatCancelar = true;
var conexaoAjax;
function chatAjax() {

    if(chatCancelar == true) {
        setTimeout(function() {
            chatAjax();
        }, 1000);
        return;
    }

    var sessao = $('#chatSessao').val();
    var chatUsuario = $('#chatUsuario').val();

    conexaoAjax = $.ajax({
        url: '/mensagem/msg',
        cache: false,
        data: {sessao: sessao,  ultimaMsg: chatMsgId},
        type: 'get',
        success: function ( json ) {
            

            var classUsuario = '';
            var nome = '';
            for ( i in json ) {
                
                classUsuario = ( parseInt(chatUsuario) == json[i].id_usuario ) ? 'usuario' : '';
                nome = ( parseInt(chatUsuario) == json[i].id_usuario ) ? 'EU' : json[i].nome;
                $('#mensagens').append('<div class="mensagem">'+
                    '<b class="' + classUsuario + '">'+ nome +':</b> ' 
                        + '<span class="data">(' + json[i].data + ') </span> <br>'+ json[i].msg +
                '</div>');

                chatMsgId = json[i].id;
            }

            $('#mensagens').scrollTop($('#mensagens')[0].scrollHeight);

            setTimeout(function() {
                chatAjax();
            }, 1000);
        },
        error: function () {
           setTimeout(function() {
                chatAjax();
           }, 1000);
        }
   });
}

$('#formChat').submit(function (e) {
    $('#formChat textarea, #formChat button').prop('disabled', true).css('cursor', 'wait');
    e.preventDefault();

    var sessao = $('#chatSessao').val();
    var msg = $('#formChat textarea').val();

    $.ajax({
        url: '/mensagem/add',
        cache: false,
        data: {sessao: sessao,  ultimaMsg: chatMsgId, msg: msg},
        type: 'post',
        success: function ( json ) {
            $('#formChat textarea, #formChat button').prop('disabled', false).css('cursor', 'default').val('');
        },
        error: function () {
            $('#formChat textarea, #formChat button').prop('disabled', false).css('cursor', 'default');
           modal('Erro ao enviar mensagem', 'erro');
        }
   });
})

$('#chatSessao').change(function () {
    var chat = $(this).val();
    $('#mensagens').html('');
    
    if( parseInt(chat) == 0) {
        chatCancelar =  true;
    }
    
    chatCancelar =  false;
    chatMsgId = 0;
});

chatAjax();