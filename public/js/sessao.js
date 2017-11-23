function cadastroSessao() {
	showModal('#modalSessao');
	$('#novaSessao').trigger('reset');
}


$('#novaSessao').submit(function(e) {
	e.preventDefault();
	ajax({
        url: '/sessao/add',
        type: 'post',
        data: $(this).serialize(),
        success: function (json) {
            $('#modalSessao').modal('hide');
			retornoOperacaoSucesso('Sessão iniciada com sucesso!'); 
			
			tr('#sessao', 'novo', [
                json.id,
				json.descricao ,
				json.data,
				'<button class="btn btn-danger"  onclick="cancelar(\'' + json.id
					+ '\')"><a class="fa fa-times"></a></button> '
            ], ['c','l','c', 'c'], 
            {
                id: 'sessao' + json.id,
                class : "link",
                'data-href' : '/sessao/'+ json.id
            });
        }
    });
});


function cancelar(id) {
	modal('Tem certeza que deseja remover este registro?', 'confirm', {
		ok: function () {
			ajax({
				url: '/sessao/del',
				type: 'post',
				data: {id: id},
				success: function (json) {
					$('#sessao' + id).remove();
					retornoOperacaoSucesso(json);    
				}
			});
		}
	})
}