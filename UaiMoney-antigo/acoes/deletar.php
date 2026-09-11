<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $pdo = new PDO('sqlite:../banco.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = :id");
        $stmt->execute([':id' => $id]);

        header("Location: ../view/dashboard.php");
        exit();
    } catch (Exception $e) {
        echo "Erro ao apagar: " . $e->getMessage();
    }
}
?>