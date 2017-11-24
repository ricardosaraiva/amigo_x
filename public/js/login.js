$('#menuLogin li a').on('click', function (e) {
    e.preventDefault();
    var formAtivar = ( $(this).html() == 'Logar' ) ? 'login' : 'registrar';

    if($(this).hasClass('active')) {
        return;
    }

    $('#login, #registrar').trigger('reset');
    $('#menuLogin li a').removeClass('active');
    $(this).addClass('active');

    $('#login, #registrar').fadeOut('slow');
    setTimeout(function () {
        $('#' + formAtivar).fadeIn('fast');
    }, 500);

});

$('#login, #registrar').submit(function (e) {
    e.preventDefault();
    
    ajax({
        url: 'usuario/' + this.id,
        type: 'post',
        data: $(this).serialize(),
        success: function (json) {

            if (this.id == 'registrar') {
                retornoOperacaoSucesso('Registro cadastrado com sucesso!');
                var email = $('#novoEmail').val();
                $('#menuLogin li a:eq(0)').click();
                $("#email").val(email);
                return false;
            }

            localStorage.removeItem('pedido');
            window.location.href = '/';
        }
    })
    
});