# php-trade-data-crawler

Crawler de alta performance usando PHP 8.3, Swoole e princípios de Clean Architecture.

## Desafio

Desenvolver um web crawler de alta performance que:
- Captura dados de sites de comércio exterior
- Processa e extrai informações relevantes de comércio
- Salva tanto o HTML bruto quanto os dados processados
- Implementa tratamento adequado de erros e logging
- Utiliza I/O assíncrono para melhor performance

## Tecnologias

- PHP 8.3
- Extensão Swoole
- Clean Architecture
- Docker
- Composer

## Instalação

```bash
# Clone o repositório
git clone https://github.com/vynazevedo/php-trade-data-crawler.git
cd php-trade-data-crawler

# Usando Docker
docker-compose up --build

# Sem Docker
composer install
php src/bootstrap.php
```

## Arquitetura

```
src/
├── Domain/          # Regras de negócio e interfaces
├── Application/     # Casos de uso
└── Infrastructure/  # Implementações externas
```

## 💻 Uso

```php
use App\Application\UseCase\CrawlWebsiteUseCase;
use App\Infrastructure\Http\SwooleWebClient;
use App\Infrastructure\Parser\ComexDataParser;
use App\Infrastructure\Storage\FileSystemStorage;

$crawler = new CrawlWebsiteUseCase(
    client: new SwooleWebClient(),
    storage: new FileSystemStorage()
);

$urls = ['https://comexstat.mdic.gov.br/pt/home'];
$results = $crawler->execute($urls);
```

## Performance

- I/O Assíncrono com Swoole
- Requisições concorrentes com corrotinas
- Uso eficiente de memória
- Pool de conexões
- Conexões keep-alive

## Clean Architecture

O projeto segue os princípios do Clean Architecture:
- Independência de frameworks
- Testabilidade
- Independência de UI
- Independência de banco de dados
- Independência de qualquer agente externo

## Testes

```bash
composer test
```
## Configuração

### Variáveis de ambiente
```env
TZ=America/Sao_Paulo
```

### Docker
```bash
# Desenvolvimento
docker-compose up --build

# Produção
docker build -t php-comexstat-crawler .
docker run -v $(pwd)/data:/app/data php-comexstat-crawler
```

### Requisitos sem Docker
- PHP >= 8.3
- Extensão Swoole >= 5.1.5
- Composer >= 2.6.5

## Estrutura dos Dados

Os dados são salvos em dois formatos:
1. HTML bruto original (`.html`)
2. Dados processados em JSON (`.json`)

Exemplo de saída JSON:
```json
{
  "exports": [
    {
      "data": "2024-01",
      "valor": 1000000,
      "pais": "CHINA"
    }
  ],
  "imports": [
    {
      "data": "2024-01",
      "valor": 800000,
      "pais": "ESTADOS UNIDOS"
    }
  ]
}
```
## Roadmap

- [ ] Adicionar suporte a proxy
- [ ] Implementar rate limiting
- [ ] Adicionar mais parsers
- [ ] Suporte a diferentes formatos de saída
- [ ] Interface web para visualização dos dados
