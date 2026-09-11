```text
UaiMoney-MVP/
│
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   ├── Repositories/
│   ├── Services/
│   └── Views/
│
├── config/
│
├── database/
│   ├── migrations/
│   └── seeds/
│
├── public/
│   ├── css/
│   ├── js/
│   ├── media/
│   └── index.php
│
├── routes/
│
├── storage/
│   ├── database/
│   └── logs/
│
└── README.md
```

A ideia de cada pasta é:

* `app/Core`: conexão, sessão, roteamento e classes-base.
* `Controllers`: recebe a requisição e decide o que fazer.
* `Services`: regras de negócio.
* `Repositories`: acesso ao banco.
* `Models`: representação das entidades.
* `Views`: telas PHP.
* `config`: configurações do sistema.
* `database/migrations`: criação e evolução das tabelas.
* `database/seeds`: dados iniciais.
* `public`: única pasta que futuramente deve ficar exposta pelo Apache.
* `routes`: definição das rotas.
* `storage/database`: banco SQLite.
* `storage/logs`: logs da aplicação.
