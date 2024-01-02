# monolog-loki-handler
Este repositório é um fork de:
https://github.com/er1z/monolog-loki-handler

# O que ele faz?
É responsável por enviar logs para o Grafana Loki em aplicações Symfony PHP < 7.4.

# Como utilizar?

Para a utilização, basta configurar o monolog do Symfony desta maneira:

Primeiro, registre o serviço, no arquivo 'services.yml' da sua aplicação Symfony, deste modo:

`services.yml`

```yaml
loki_handler:
        class: Er1z\MonologLokiHandler\LokiHandler
        arguments:
            $entrypoint: '%env(GRAFANA_LOKI_URL)%'
            $channel: '%env(APP_NAME)%'
```

**Nota:** é necessário configurar as duas variáveis de ambiente acima, sendo:

| Parâmetro     | Valor                            | Descrição |
|---------------|----------------------------------|-----------
| `entrypoint`  | `GRAFANA_LOKI_URL`        | Endereço do LOKI para envio das informações
| `channel`     | `APP_NAME`                | Nome da aplicação, para fazer a filtragem dos logs no GRAFANA
 

E configurar no arquivo config.yml o handler do monologo, vide exemplo abaixo:

`config.yml`
```yaml
monolog:
    handlers:

        main:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%-debug.log"
            level: debug
            channels: ["!event"]
        loki:
            type: service
            id: loki_handler
            channels: ["!doctrine", "!console", "!event", "!request", "!security"]
```


Por fim, feito as devidas configurações, o LOKI receberá os eventos.
