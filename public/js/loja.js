$(".formProduto").submit(function (e) {
    e.preventDefault();

    var id = $(this).children('input[name="id"]').val();
    var valor = $(this).children('input[name="valor"]').val();
    var descricao = $(this).children('input[name="descricao"]').val();
    var qtd = $(this).children('input[name="qtd"]').val();
    
    var pedido = localStorage.getItem("pedido");

    $(this).children('input[name="qtd"]').val('');
    
    if(pedido == null) {
        localStorage.setItem("pedido", JSON.stringify([]));
        pedido = localStorage.getItem("pedido");
    }

    if( pedido == null ) {
        modal("Erro ao adicionar item ao carrinho!")
        return;
    }

    pedido = JSON.parse(pedido);

    pedido.push({
        id: id,
        valor: valor,
        descricao: descricao,
        qtd: qtd
    });

    localStorage.setItem("pedido", JSON.stringify(pedido));

    calcularTotalPedido();
    retornoOperacaoSucesso("Produto adiocionado ao carrinho com sucesso!");
});

function carrinho ( remover ) {
    var pedidos = ( localStorage.getItem("pedido") == null ) ? 0 : JSON.parse(localStorage.getItem("pedido"));
    
    if(pedidos.length == 0) {
        modal('Nenhum produto no carrinho!', 'erro');
        return false;
    }

    $("#carrinhoPedidos").html('');


    var total = 0;
    for( pedido in pedidos ) {
        total = total + (parseFloat(pedidos[pedido].qtd) * parseFloat(pedidos[pedido].valor));

        tr("#carrinhoPedidos", 'novo', [
            pedidos[pedido].descricao,
            pedidos[pedido].qtd,
            moeda(pedidos[pedido].valor, 2),
            '<button class="btn btn-danger" type="buttom" onclick="carrinhoRemover(' + pedido + ')"><i class="fa fa-times"></i></danger>',
        ], 
            ['l','c','r','c'],
            {
                id: ('carrinhoPedidos' + pedido.toString())
            }
        );        
    }

    $('#carrinhoTotal').html('Total R$: ' + moeda(total, 2));

    if(remover != true) {
        $('#carrinho').trigger('reset');
        showModal("#modalCarrinho");
        return;
    }

    calcularTotalPedido();
    
}

function carrinhoRemover(i) {
    var pedido = JSON.parse(localStorage.getItem("pedido"));
    pedido.splice(i , 1);
    localStorage.setItem("pedido", JSON.stringify(pedido));

    calcularTotalPedido();
    if(pedido.length == 0) {
        $("#modalCarrinho").modal('hide');
        return;
    }

    carrinho(true);
}

function calcularTotalPedido() {
    var itens = ( localStorage.getItem("pedido") == null ) ? 0 : JSON.parse(localStorage.getItem("pedido")).length;
    $('#carrinhoTotalItens').html(itens.toString());
}

calcularTotalPedido();