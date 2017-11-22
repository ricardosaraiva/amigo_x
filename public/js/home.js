function aceitarConvite(id) {
    ajax({
        url: '/grupo/aceitar',
        type: 'post',
        data: {id: id},
        success: function (json) {
            $('#grupo' + id).remove();
            retornoOperacaoSucesso('Convite aceito com sucesso!');
        }
    }); 
}