<?php
date_default_timezone_set('America/Sao_Paulo');

try {
    // Como o script está na pasta db, voltamos uma pasta para achar o banco na raiz
    $pdo = new PDO('sqlite:../banco.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Tabela de Subgrupos com classificação completa
    $pdo->exec("CREATE TABLE IF NOT EXISTS subgrupos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,       -- 'Entrada' ou 'Saída'
        grupo TEXT NOT NULL,      -- Ex: 'Essencial', 'Lazer', 'Investimento', 'Renda Fixa'
        nome TEXT NOT NULL,       -- Ex: 'Supermercado', 'Aluguel', 'Salário'
        descricao TEXT            -- Opcional
    )");

    // 2. Tabela de Transações com o campo de banco/cartão incluído
    $pdo->exec("CREATE TABLE IF NOT EXISTS transacoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,
        data TEXT NOT NULL,
        valor REAL NOT NULL,
        grupo TEXT NOT NULL,
        subgrupo TEXT NOT NULL,
        forma_pagamento TEXT,
        banco_cartao TEXT,
        descricao TEXT
    )");

    echo "<div style='font-family:Segoe UI; background:#1c2541; color:#34d399; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Sucesso, Uai! 🚀</h3>
            <p>Tabelas <b>subgrupos</b> e <b>transacoes</b> criadas/atualizadas com sucesso na base de dados!</p>
            <p>Voltar para o <a href='../dashboard.php' style='color:#38bdf8;'>Painel Principal</a>.</p>
          </div>";
} catch (Exception $e) {
    echo "<div style='font-family:Segoe UI; background:#1c2541; color:#f87171; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Eita! Erro ao criar tabelas:</h3>
            <p>" . $e->getMessage() . "</p>
          </div>";
}
?>