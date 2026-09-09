let filtroAtivo = null;

function filtrarTabela(tipo) {
    const linhas = document.querySelectorAll('.linha-transacao');
    const cardEntradas = document.getElementById('card-entradas');
    const cardSaidas = document.getElementById('card-saidas');
    const tituloTabela = document.getElementById('titulo-tabela');
    const dataOriginal = "<?php echo date('d/m/Y', strtotime($data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($data_fim)); ?>";

    // Se clicar no mesmo card que já estava ativo, remove o filtro
    if (filtroAtivo === tipo) {
        filtroAtivo = null;
        linhas.forEach(linha => linha.style.display = ''); // Mostra tudo

        // Restaura o visual dos cards e do título
        cardEntradas.style.opacity = '1';
        cardSaidas.style.opacity = '1';
        tituloTabela.querySelector('span:nth-child(2)').innerHTML = `Transações de ${dataOriginal}`;
        return;
    }

    // Ativa o novo filtro
    filtroAtivo = tipo;

    linhas.forEach(linha => {
        if (linha.getAttribute('data-tipo') === tipo) {
            linha.style.display = ''; // Mostra a linha
        } else {
            linha.style.display = 'none'; // Esconde a linha
        }
    });

    // Efeito visual de destaque nos cards
    if (tipo === 'Entrada') {
        cardEntradas.style.opacity = '1';
        cardSaidas.style.opacity = '0.3'; // Apaga o vermelho
        tituloTabela.querySelector('span:nth-child(2)').innerHTML = `Apenas Entradas (${dataOriginal})`;
    } else {
        cardEntradas.style.opacity = '0.3'; // Apaga o verde
        cardSaidas.style.opacity = '1';
        tituloTabela.querySelector('span:nth-child(2)').innerHTML = `Apenas Saídas (${dataOriginal})`;
    }
}