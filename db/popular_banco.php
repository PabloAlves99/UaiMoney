<?php
date_default_timezone_set('America/Sao_Paulo');

try {
    // Conecta ao banco voltando uma pasta para a raiz do projeto
    $pdo = new PDO('sqlite:../banco.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Garante que a tabela subgrupos existe antes de inserir
    $pdo->exec("CREATE TABLE IF NOT EXISTS subgrupos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,
        grupo TEXT,
        nome TEXT NOT NULL,
        descricao TEXT
    )");

    // Lista de subgrupos padrão para injetar no banco
    $subgrupos_iniciais = [
        // --- ENTRADAS ---
        ['Entrada', 'Renda Fixa', 'Salário', 'Salário mensal principal'],
        ['Entrada', 'Renda Fixa', '13º Salário / Férias', 'Benefícios anuais'],
        ['Entrada', 'Renda Variável', 'Freelance / Extras', 'Trabalhos extra e bicos'],
        ['Entrada', 'Renda Variável', 'Dividendos / Investimentos', 'Retorno de aplicações'],

        // --- SAÍDAS (ESSENCIAIS) ---
        ['Saída', 'Essencial', 'Supermercado', 'Compras de mantimentos e feira'],
        ['Saída', 'Essencial', 'Aluguel / Habitação', 'Prestação da casa ou aluguel'],
        ['Saída', 'Essencial', 'Energia Elétrica', 'Conta de luz CEMIG'],
        ['Saída', 'Essencial', 'Água e Esgoto', 'Conta de água'],
        ['Saída', 'Essencial', 'Internet / Telefonia', 'Planos de dados e fibra óptica'],
        ['Saída', 'Essencial', 'Saúde / Farmácia', 'Planos de saúde e remédios'],
        ['Saída', 'Essencial', 'Transporte / Combustível', 'Gasolina, Uber ou transporte público'],

        // --- SAÍDAS (LAZER & ESTILO DE VIDA) ---
        ['Saída', 'Lazer', 'Restaurantes / Delivery', 'Ifood, bares e passeios'],
        ['Saída', 'Lazer', 'Streaming / Assinaturas', 'Netflix, Spotify, Cloud, AI'],
        ['Saída', 'Lazer', 'Jogos / Eletrônicos', 'Steam, acessórios e PC gaming'],
        ['Saída', 'Lazer', 'Vestuário / Calçados', 'Roupas, tênis Adidas, acessórios'],

        // --- SAÍDAS (OUTROS) ---
        ['Saída', 'Investimento', 'Aporte Mensal', 'Guardar dinheiro / CDB / Ações'],
        ['Saída', 'Dívidas / Empréstimos', 'Cartão de Crédito Fatura', 'Pagamento de faturas anteriores']
    ];

    $stmt = $pdo->prepare("INSERT INTO subgrupos (tipo, grupo, nome, descricao) VALUES (:tipo, :grupo, :nome, :descricao)");

    $inseridos = 0;
    foreach ($subgrupos_iniciais as $s) {
        // Verifica se já existe para não duplicar
        $checa = $pdo->prepare("SELECT COUNT(*) FROM subgrupos WHERE tipo = ? AND nome = ?");
        $checa->execute([$s[0], $s[2]]);
        if ($checa->fetchColumn() == 0) {
            $stmt->execute([
                ':tipo' => $s[0],
                ':grupo' => $s[1],
                ':nome' => $s[2],
                ':descricao' => $s[3]
            ]);
            $inseridos++;
        }
    }

    echo "<div style='font-family:Segoe UI; background:#1c2541; color:#34d399; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Sucesso, Uai! 🚀</h3>
            <p>Foram inseridos <b>{$inseridos}</b> subgrupos padrão na base de dados com sucesso.</p>
            <p>Pode voltar para o <a href='../dashboard.php' style='color:#38bdf8;'>Painel Principal</a>.</p>
          </div>";

} catch (Exception $e) {
    echo "<div style='font-family:Segoe UI; background:#1c2541; color:#f87171; padding:20px; border-radius:10px; margin:20px;'>
            <h3>Eita! Erro ao popular banco:</h3>
            <p>" . $e->getMessage() . "</p>
          </div>";
}
?>