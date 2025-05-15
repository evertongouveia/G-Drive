$(document).ready(function() {
    // Confirmar exclusão de usuário
    $('a.btn-danger').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Esta ação não pode ser desfeita!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // Animação ao carregar pastas e arquivos
    $('.col-md-3').each(function(index) {
        $(this).delay(100 * index).fadeIn(500);
    });

    // Validação em tempo real do nome de usuário
    $('#username').on('input', function() {
        const username = $(this).val();
        if (username.length > 2) {
            $.ajax({
                url: 'check_username.php',
                method: 'POST',
                data: { username: username },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        $('#usernameError').removeClass('d-none');
                        $('#addUserForm button[type="submit"]').prop('disabled', true);
                    } else {
                        $('#usernameError').addClass('d-none');
                        $('#addUserForm button[type="submit"]').prop('disabled', false);
                    }
                }
            });
        }
    });

    // Toggle sidebar em dispositivos móveis
    $('[data-bs-target="#sidebar"]').on('click', function() {
        $('#sidebar').toggleClass('show');
    });

    // Fechar sidebar ao clicar fora em dispositivos móveis
    $(document).on('click', function(e) {
        if ($(window).width() < 768 && !$(e.target).closest('#sidebar').length && !$(e.target).closest('[data-bs-target="#sidebar"]').length) {
            $('#sidebar').removeClass('show');
        }
    });
});