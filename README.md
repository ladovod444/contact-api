# Contact Api

## Требования

Для запуска проекта необходимы:

- Docker Engine (версия ≥ 20.10)
- Docker Compose (плагин или отдельная утилита; в современных Docker уже встроен как `docker compose`)


## 1. Как запустить проект:
### Инструкция по установке и запуску

 1. склонировать проект

 ``` git clone git@github.com:ladovod444/contact-api.git ```

 2.   зайти в папку contact-api и выполнить:

 ``` docker-compose up -d ```

 
### Настройка переменных окружения
#### В корне проекта в .env добавить следующие переменные, необходимо вместо <SITE_EMAIL> указать реальное значение и так для всех остальных:
```env
DATABASE_URL="postgresql://<DB_USER>:<DB_PASSWORD>@<DB_HOST>:<DB_PORT>/<DB_NAME>?serverVersion=16&charset=utf8"
MAILER_DSN="smtp://<MAIL_USER>:<MAIL_PASSWORD>@smtp.yandex.ru:465?encryption=ssl"
AI_API_KEY="<AI_API_KEY>"
AI_API_URL="<AI_API_URL>"
SITE_EMAIL="<SITE_EMAIL>"
API_VERSION="1.0"
```

### Команды для установки зависимостей
1. Нужно выполнить команду

``` docker-compose exec php_contact composer install ```

2. Создать миграции

``` docker-compose exec php_contact php bin/console doctrine:migrations:diff ```

3. Выполнить миграции
``` docker-compose exec php_contact php bin/console doctrine:migrations:migrate ```

Сервис будет доступен по адресу: http://localhost:8090

Документация по API доступна по адресу: http://localhost:8090/api/doc


## 2. Стек технологий:
### Backend: PHP 8.4, Symfony
#### Библиотеки/компоненты:
   1. `symfony/validator`
   2. `doctrine/doctrine-bundle и doctrine/orm`
   3.  `symfony/serializer-pack`
   4.  `symfony/mailer`
   5. `symfony/http-client`
   6. `symfony/rate-limiter`
   7. `symfony/monolog-bundle`
   8. `nelmio/api-doc-bundle`
   9. `symfony/twig-bundle`
   10. `symfony/asset`
   11. `twig/cssinliner-extra`

### AI: какие инструменты использованы
Для AI‑интеграции использованы: HTTP‑клиент (symfony/http-client) и агрегатор AI‑API (AITunnel) - модель Сlaude Opus 4.8

## 3. Архитектура:
### Ключевые слои
- **API**: `src/Controller/Api/` — обработка HTTP‑запросов.
- **Domain/Application**: DTO, Use Cases и интерфейсы сервисов — чистая бизнес‑логика без зависимостей от фреймворка.
- **Infrastructure**: реализации сервисов, Entity, Repository — взаимодействие с БД, внешними API и почтой.

### Дерево файлов
````
src/
├── Controller/
│ └── Api/
│ └── ContactsController.php
├── DTO/
│ ├── AiAnalysisDTO.php
│ └── ContactDTO.php
├── Entity/
│ └── ContactStatistics.php
├── Repository/
│ └── ContactStatisticsRepository.php
└── Services/
├── Ai/
│ ├── ContactAiService.php
│ └── ContactAiServiceInterface.php
├── Mail/
│ ├── ContactEmailService.php
│ └── ContactEmailServiceInterface.php
├── Statistics/
│ ├── ContactStatisticsServiceHandler.php
│ └── ContactStatisticsServiceInterface.php
├── ProcessContactRequestInterface.php
└── ProcessContactRequestUseCase.php
````

### Паттерны проектирования
1. MVC (Model-View-Controller).

2. Dependency Injection (Внедрение зависимостей) / IoC-контейнер.

3. Repository (Репозиторий).

4. Data Mapper. Doctrine (стандартная ORM для Symfony)

5. DTO

### Выбор технологий 

Стек подобран так, чтобы чётко разделить домен и инфраструктуру:

    Symfony даёт готовые инфраструктурные компоненты (контроллеры, роутинг, DI), не навязывая бизнес‑логике свою структуру.

    PostgreSQL выступает как репозиторий данных: сущности и репозитории абстрагированы от SQL, что позволяет при необходимости заменить хранилище.

## 4. Реализация API:
### Описание эндпоинтов
1. POST /api/contact — приём и обработка заявки из формы обратной связи: валидация данных, отправка уведомлений (пользователю и владельцу), 
сохранение статистики, вызов AI‑анализа тональности. Возвращает статус отправки и созданую сущность ContactStatistics (в json-формате).
    Пример запроса:
     ````
    curl -X POST http://localhost:8090/api/contact \
      -H "Content-Type: application/json" \
      -d '{
        "name": "Иван Петров",
        "phone": "+7 (999) 123-45-67",
        "email": "ivan@example.com",
        "comment": "Хочу оставить отзыв о работе сервиса. Всё понравилось, спасибо!"
      }'
     ````

   Ответ (в задаче просто отдаю созданную сущность ContactStatistics (в json-формате), в реальных проектах можно выбирать группы c помощью #Groups (Symfony\Component\Serializer\Attribute\Groups) компонента Serializer ):
      ````
        {
            "id": 19,
            "name": "Иван Петров",
            "phone": "+7 (999) 123-45-67",
            "email": "ivan@example.com",
            "comment": "Хочу оставить отзыв о работе сервиса. Всё понравилось, спасибо!",
            "sentiment": "positive",
            "category": "support",
            "autoReply": "Спасибо за ваш тёплый отзыв! Нам очень приятно, что вам всё понравилось. Будем рады видеть вас снова!",
            "ip": "172.24.0.1"
        }
      ````

2. GET /api/metrics — отдача метрик по обращениям (количество заявок, распределение по статусам/категориям, динамика). 

    Пример использования: 
    
    2.1 Все метрики /api/metrics

        ````
        curl http://localhost:8090/api/metrics
        ````
    2.2 По датам (от - до) 

        ````
        http://localhost:8090/api/metrics?dateFrom=2025-06-01&dateTo=2026-08-01
       ````
    Ответ:
     ````
     {
    "metrics": [
        {
            "sentiment": "negative",
            "category": "support",
            "total": 1
        },
        {
            "sentiment": "positive",
            "category": "support",
            "total": 8
        },
        {
            "sentiment": "neutral",
            "category": "support",
            "total": 1
        }
        ]
    }
     ````

3. GET /api/health — проверка работоспособности сервиса: в данный момент - доступность БД.
   Пример использования:

        ````
        curl http://localhost:8090/api/health
        ````
   Ответ:

           ````
        {
           "status": "healthy",
           "timestamp": "2026-07-23T11:14:17+00:00",
           "components": {
             "database": "ok"
           },
           "version": "1.0"
        }
       ````

### Валидация и обработка ошибок

1. Валидация входных данных: symfony/validator применяется к DTO на границе API, 
    чтобы отсекать некорректные запросы до выполнения бизнес‑логики.

2. Обработка исключений: доменные исключения выбрасываются из Use Case и сервисов при нарушении бизнес‑правил. 
   Глобальный обработчик (error handler / exception listener) перехватывает их, логирует и возвращает 
   стандартизированные JSON‑ответы с соответствующими HTTP‑кодами (400, 429, 500).



## 5. AI-интеграция:
### Какие AI‑инструменты и для чего
 1. AI‑сервис (AITunnel API, выбирал модель Сlaude Opus 4.8 ) через symfony/http-client — для анализа входящих комментариев:
    определение тональности (sentiment), классификация по категориям (category) и генерация автоответа (autoReply).
    Роль в архитектуре: вынесена в отдельный сервис (ContactAiService) с интерфейсом — это позволяет подменять реализацию 
    (например, на локальный моковый сервис в тестах или на OpenAI в другой среде) без изменений в Use Case.
 
 2. DTO‑контракты: результат нормализуется в AiAnalysisDTO — так домен не зависит от формата ответа конкретного AI‑провайдера.
### Промпты:

  ````
  Ты аналитик обратной связи. Верни ответ строго в виде JSON без какой‑либо разметки, без ```json, без лишних слов. Только JSON-объект строго по схеме: {"sentiment": "positive|negative|neutral", "category": "billing|support|bug|feature", "autoReply": "текст"}
  ````
 этого достаточно для задачи.


## 6. Что сделано с помощью AI:

6.1 src/Controller/Api/HealthController.php - было сгенериовано с помощью Qwen 3.7. Инструмент предложил также статусы 
 по кешированию c помощью Redis, и ClickHouse, но т.к. это не входит в ТЗ, оставил и отредактировал только для БД, но, если потребуется
 указать другие статусы, то лучше будет создать отдельный Service.
 Промпт - для данного эндпоинта максимально простой "Напиши контроллер Symfony для эндпоинта GET /api/health"

6.2 src/Controller/Api/MetricsController.php - было сгенериовано с помощью Qwen 3.7

Промпт -
    ````
    Необходимо создать минимальный стек для эндпоинта GET /api/metrics в Symfony: 
        entity ContactStatistics, 
        repository с методом для агрегации (GROUP BY по sentiment/category), 
        controller, который принимает параметры from и to, вызывает репозиторий и возвращает JSON.
    В сущности ContactStatistics нужно сохранять: ip, name, phone, email, comment, sentiment, category, autoReply, createdAt.
    Необходимо логирование.
    ````
Вручную исправил название параметров 'to' на 'dateTo' и 'from' на 'dateFrom', т.е. хотелось более конкретных названий, а также отформатировал код

Для репозитория src/Repository/ContactStatisticsRepository были указаны простые промпты:

 "Уточни как лучше организовать группировку для запроса выборки ContactStatistics"


6.3 src/Controller/ContactsController.php - Контроллер для демо страницы "Форма контактов" - было сгенериовано с помощью Qwen 3.7

Промпт - 

    Необходимо создай демо‑страницу формы обратной на Vue.js.
    Контроллер: ContactDemoController с двумя методами:
    
    index() — рендерит шаблон.
     на DTO ContactDTO, вызывает Use Case ProcessContactRequestUseCase, возвращает JSON (успех/ошибки).
    Twig‑шаблон: index.html.twig. Включи форму с полями: name, email, comment. 
    Добавить блок для отображения ошибок валидации и статус‑сообщение после отправки.
    
    Vue.js (встроенный в шаблон):
    поля формы name, email, comment, errors, status;
    метод submitForm(): делает fetch на /api/contact, обрабатывает JSON‑ответ;
    простая блокировка кнопки во время запроса.
    
AI выдал смешанный код (html, js и css) для templates/contacts/index.html.twig.
После уточняющих вопросов, было созданы отдельно public/js/contact-form.js, public/css/contact.css, и подключены в шаблон

6.4 src/Controller/Api/ContactController.php - основная чать кода была написана вручную. 
Для ProcessContactRequest нужно было посоветоваться как лучше разделить логику анализа сообщения через AI, отправку email, создания сущности.

Промпт

    Имеется контроллер ContactController, который принимает POST‑запрос на /api/contact, валидирует данные через ContactDTO (с помощью #[MapRequestPayload]), 
    применяет rate‑limiting по IP и затем вызывает $contactRequest->execute($dto, $clientIp).

    Сейчас в контроллере смешаны инфраструктурные задачи (rate‑limit, получение IP) и бизнес‑поток. Перепиши архитектуру так, чтобы контроллер остался максимально тонким: он должен только:
    
    получить IP;
    проверить rate‑limit;
    вызвать один метод сервиса;
    вернуть JSON.
    Всю бизнес‑логику (сохранение статистики, вызов AI‑сервиса, отправку email) неоходимо вынеси в реализацию ProcessContactRequestInterface. 
    Нужно создать реализацию ProcessContactRequestUseCase, которая:
    будет принимать ContactDTO и string $clientIp;
    последовательно вызывать сервисы: ContactStatistics, ContactAi, ContactEmail;
    возвращает Entity статистики ContactStatistics.
    
После предложенного AI, были вручную исправлены имена классов сервисов и методов, немного исправлена логика формирования email

## 7. Хранение данных
### Логи хранятся в папке var/log, реализовано с помощью компонента symfony/monolog-bundle,

для этого компонента него указаны настройки в config/packages/monolog.yaml, созданы каналы и хендлеры логирования, что позволяет
для каждого сервиса/контроллера при указании аттрибута WithMonologChannel, с названием канала, например: #[WithMonologChannel('contact')]
указывать логи в разные файлы. В данномо случае contact.log, health.log, var/log/metrics.log

### Rate limiting, реализовано с помощью компонента symfony/rate-limiter
для этого компонента него указаны настройки в config/packages/rate_limiter.yaml, указан ключ contact_api, который используется в
src/Controller/Api/ContactController.php, policy: 'fixed_window — это стратегия «фиксированного окна» 
в Symfony Rate Limiter: лимит считается в жёстко заданных временных отрезках за интервал времени 1 минута разрешено ровно 10 запросов.

### Статистика храниться в БД в таблица public.contact_statistics
Для этих целей была создана сущность src/Entity/ContactStatistics.php, 
за бизнес-логику отвечает сервис src/Services/Statistics/ContactStatisticsServiceHandler.php


## 8. Дополнительно - фронтенд (demo only)
Для демонстрации работы /api/contact была создана страница /contacts, с выводом формы на Vue.js
