## Como Rodar o Projeto

### Pré-requisitos

Certifique-se de ter os seguintes softwares instalados em sua máquina:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Passos para Rodar o Projeto

1. **Clone o repositório:**

   ```sh
   git clone https://github.com/seu-usuario/seu-repositorio.git
   cd seu-repositorio
   docker-compose build app   
   docker-compose up -d
   docker exec -ti precpago_app php artisan statistics:consume   
   ```

   ### Acesse a aplicação:
    A aplicação estará disponível em http://localhost:8181

### Tecnologias utilizadas

- NGINX
- PHP_FPM
- RabbitMQ 