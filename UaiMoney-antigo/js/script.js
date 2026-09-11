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

// 1. Variável de controle do Card (Entrada/Saída)
let filtroCardAtivo = null;

function filtrarTabela(tipo) {
    if (filtroCardAtivo === tipo) {
        filtroCardAtivo = null; // Clicou no mesmo card, remove o filtro
    } else {
        filtroCardAtivo = tipo; // Define o novo filtro
    }
    aplicarFiltros();
}

// 2. Lógica Combinada (Global + Colunas + Cards)
function aplicarFiltros() {
    const linhas = document.querySelectorAll('.linha-transacao');
    const termoGlobal = document.getElementById('filtroGlobal').value.toLowerCase();
    const inputsColuna = document.querySelectorAll('.input-filtro-coluna');

    linhas.forEach(linha => {
        let mostrar = true;

        // A. Verifica Filtro dos Cards
        const tipoLinha = linha.getAttribute('data-tipo');
        if (filtroCardAtivo && tipoLinha !== filtroCardAtivo) {
            mostrar = false;
        }

        // B. Verifica Filtro Global
        if (mostrar && termoGlobal !== '') {
            const textoLinha = linha.textContent.toLowerCase();
            if (!textoLinha.includes(termoGlobal)) {
                mostrar = false;
            }
        }

        // C. Verifica Filtros por Coluna
        if (mostrar) {
            inputsColuna.forEach(input => {
                const termoColuna = input.value.toLowerCase();
                if (termoColuna !== '') {
                    const indiceColuna = input.getAttribute('data-col');
                    const celula = linha.cells[indiceColuna];
                    if (celula && !celula.textContent.toLowerCase().includes(termoColuna)) {
                        mostrar = false;
                    }
                }
            });
        }

        linha.style.display = mostrar ? '' : 'none';
    });
}

// 3. Ordenação Matemática e Alfabética das Colunas
let ordemAscendente = true;
let colunaOrdenadaAnterior = -1;

function ordenarTabela(indiceColuna) {
    const tabelaBody = document.querySelector('#tabelaTransacoes tbody');
    const linhas = Array.from(tabelaBody.querySelectorAll('.linha-transacao'));

    if (colunaOrdenadaAnterior !== indiceColuna) {
        ordemAscendente = true; // Reseta a ordem ao mudar de coluna
    }

    linhas.sort((a, b) => {
        let valA = a.cells[indiceColuna].textContent.trim().toLowerCase();
        let valB = b.cells[indiceColuna].textContent.trim().toLowerCase();

        // Tratamento Inteligente para Data (dd/mm/yyyy -> yyyymmdd)
        if (indiceColuna === 0) {
            valA = valA.split('/').reverse().join('');
            valB = valB.split('/').reverse().join('');
        }
        // Tratamento Inteligente para Moeda (R$ 1.500,00 -> 1500.00)
        else if (indiceColuna === 6) {
            const limpaMoeda = (str) => parseFloat(str.replace(/[^0-9,-]+/g, '').replace(',', '.')) || 0;
            valA = limpaMoeda(valA);
            valB = limpaMoeda(valB);
            return ordemAscendente ? valA - valB : valB - valA;
        }

        // Comparação padrão para texto
        if (valA < valB) return ordemAscendente ? -1 : 1;
        if (valA > valB) return ordemAscendente ? 1 : -1;
        return 0;
    });

    linhas.forEach(linha => tabelaBody.appendChild(linha));
    ordemAscendente = !ordemAscendente;
    colunaOrdenadaAnterior = indiceColuna;
}