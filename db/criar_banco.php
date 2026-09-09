<?php
date_default_timezone_set('America/Sao_Paulo');

try {
    // Como o script está na pasta db, voltamos uma pasta para achar o banco na raiz
    $pdo = new PDO('sqlite:../banco.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Criar a nova tabela de Grupos (Personalizados)
    $pdo->exec("CREATE TABLE IF NOT EXISTS grupos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        tipo TEXT NOT NULL
    )");

    // 2. Tabela de Subgrupos com classificação completa
    $pdo->exec("CREATE TABLE IF NOT EXISTS subgrupos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,       -- 'Entrada' ou 'Saída'
        grupo TEXT NOT NULL,      -- Ex: 'Essencial', 'Lazer', 'Investimento', 'Renda Fixa'
        nome TEXT NOT NULL,       -- Ex: 'Supermercado', 'Aluguel', 'Salário'
        descricao TEXT            -- Opcional
    )");

    // 3. Tabela de Transações original
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

    // 4. Trava de Segurança: Adicionar colunas de recorrência caso ainda não existam
    $colunasNovas = [
        "tipo_registro" => "TEXT DEFAULT 'unico'", // 'unico', 'pai' ou 'parcela'
        "numero_parcela" => "INTEGER DEFAULT 1",
        "total_parcelas" => "INTEGER DEFAULT 1",
        "id_grupo" => "INTEGER"
    ];

    $result = $pdo->query("PRAGMA table_info(transacoes)");
    $colunasAtuais = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $colunasAtuais[] = $row['name'];
    }

    foreach ($colunasNovas as $coluna => $tipo) {
        if (!in_array($coluna, $colunasAtuais)) {
            $pdo->exec("ALTER TABLE transacoes ADD COLUMN $coluna $tipo");
        }
    }


    echo "<div style='font-family:Segoe UI; background:#1c2541; color:#34d399; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Sucesso, Uai! 🚀</h3>
            <p>Tabelas <b>grupos</b>, <b>subgrupos</b> e <b>transacoes</b> criadas e atualizadas para suportar recorrência!</p>
            <p>Voltar para o <a href='../view/dashboard.php' style='color:#38bdf8;'>Painel Principal</a>.</p>
          </div>";

} catch (Exception $e) {
    echo "<div style='font-family:Segoe UI; background:#1c2541; color:#f87171; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Eita! Erro ao criar tabelas:</h3>
            <p>" . $e->getMessage() . "</p>
          </div>";
}
?>