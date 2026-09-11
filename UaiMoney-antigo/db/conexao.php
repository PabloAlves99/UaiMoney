<?php
// Configuração global de fuso horário
date_default_timezone_set('America/Sao_Paulo');

try {
    // Como está dentro da pasta 'db', usa '../banco.sqlite' se o projeto estiver na raiz, 
    // ou se preferir o banco dentro da pasta db, aponta para 'banco.sqlite'. 
    // Como combinamos que o banco está na raiz, usamos '../banco.sqlite':
    $pdo = new PDO('sqlite:../banco.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Garante que a tabela 'subgrupos' existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS subgrupos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,
        grupo TEXT,
        nome TEXT NOT NULL,
        descricao TEXT
    )");

    // Migração de segurança para subgrupos (coluna 'grupo')
    $colunasSub = $pdo->query("PRAGMA table_info(subgrupos)")->fetchAll(PDO::FETCH_ASSOC);
    $temGrupoSub = false;
    foreach ($colunasSub as $col) {
        if ($col['name'] === 'grupo') {
            $temGrupoSub = true;
            break;
        }
    }
    if (!$temGrupoSub) {
        $pdo->exec("ALTER TABLE subgrupos ADD COLUMN grupo TEXT");
    }

    // 2. Garante que a tabela 'transacoes' existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS transacoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,
        data TEXT NOT NULL,
        valor REAL NOT NULL,
        grupo TEXT,
        subgrupo TEXT NOT NULL,
        forma_pagamento TEXT,
        banco_cartao TEXT,
        descricao TEXT
    )");

    // Migrações de segurança para transacoes (colunas 'grupo' e 'banco_cartao')
    $colunasTrans = $pdo->query("PRAGMA table_info(transacoes)")->fetchAll(PDO::FETCH_ASSOC);
    $temGrupoTrans = false;
    $temBancoTrans = false;
    foreach ($colunasTrans as $col) {
        if ($col['name'] === 'grupo') {
            $temGrupoTrans = true;
        }
        if ($col['name'] === 'banco_cartao') {
            $temBancoTrans = true;
        }
    }
    if (!$temGrupoTrans) {
        $pdo->exec("ALTER TABLE transacoes ADD COLUMN grupo TEXT");
    }
    if (!$temBancoTrans) {
        $pdo->exec("ALTER TABLE transacoes ADD COLUMN banco_cartao TEXT");
    }

} catch (Exception $e) {
    die("<div style='font-family:Segoe UI; background:#1c2541; color:#f87171; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Eita! Erro crítico de conexão com a base de dados:</h3>
            <p>" . $e->getMessage() . "</p>
         </div>");
}
?>