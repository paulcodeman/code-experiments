# Проверка переноса

Дата снимка: 14 ноября 2026 года.

## Первая группа: исходные снимки

| Источник | Каталог | Исходный master | Коммитов | Файлов |
| --- | --- | --- | ---: | ---: |
| [binaryscript](https://github.com/paulcodeman/BinaryScript) | `binaryscript/` | `40029e2bfe15feb59d61df51d9d1493ffd8c6a2b` | 8 | 9 |
| [pythonjs](https://github.com/paulcodeman/pythonjs) | `pythonjs/` | `113e5a3289ac04c80ed42c961ca0f9c02b9dd424` | 5 | 10 |
| [python-code-display](https://github.com/paulcodeman/python-code-display) | `python-code-display/` | `e8bec523bf8d460a99ed11d09692d048fd82e2c1` | 7 | 2 |

В каждом источнике была одна ветка `master`; исходных тегов не было.
Её вершина сохранена тегом `source/<каталог>/master`.

## Сохранение истории и файлов

Каждый проект добавлен через `git subtree add --prefix=<каталог>` без `--squash`.
Исходные объекты коммитов включены в граф `main` без переписывания: сохранены SHA,
авторы, коммитеры, даты, сообщения, родители и деревья. Добавлены новые коммиты
документации и объединения. Старые коммиты содержат исходные пути от корня;
история под новым префиксом начинается с коммита импорта.

Для каждого проекта проверено точное равенство Git-дерева исходного `master`
дереву соответствующей подпапки. Это включает имена файлов, режимы и blob SHA.
Дополнительно побайтово сравнены все 21 файл рабочего дерева, а также сырые объекты
всех 20 исходных коммитов; подтверждена достижимость каждого из них из `main`.
Исходные README сохранены. Файлы лицензий не обнаружены; лицензии не добавлялись.

## Независимая проверка

После клонирования репозитория выполните:

```sh
git rev-parse source/binaryscript/master^{tree} main:binaryscript
git merge-base --is-ancestor 40029e2bfe15feb59d61df51d9d1493ffd8c6a2b main
git log --format=fuller source/binaryscript/master
git rev-parse source/pythonjs/master^{tree} main:pythonjs
git merge-base --is-ancestor 113e5a3289ac04c80ed42c961ca0f9c02b9dd424 main
git log --format=fuller source/pythonjs/master
git rev-parse source/python-code-display/master^{tree} main:python-code-display
git merge-base --is-ancestor e8bec523bf8d460a99ed11d09692d048fd82e2c1 main
git log --format=fuller source/python-code-display/master
git fsck --full
```

В каждой паре `rev-parse` должны совпасть два хеша дерева.
`merge-base --is-ancestor` должен завершиться с кодом 0.
Для просмотра изменений исходного файла используйте его старый путь и тег источника,
например `git log source/pythonjs/master -- js.php`.

## Сохранённые бинарники

| Файл | Размер, байт | SHA-256 |
| --- | ---: | --- |
| `pythonjs/python.exe` | 97944 | `d9185cd043c63dd2187a814bcef6e13922ce6fde5d7f694e54668f770fb7048c` |
| `pythonjs/python36.dll` | 3303064 | `927b879e8877e332e7580944fcae65d767a894fbcbd968b2b57199800eaf98cf` |

Максимальный размер исторического blob первой группы: 3 303 064 байта.
Бинарники сохранены как обычные Git-объекты, без LFS. Они не запускались.
Функциональные тесты и аудит безопасности, совместимости, происхождения бинарников
или полноты прав на распространение не проводились. Известные ограничения перечислены в README.

## Границы переноса

При первом переносе источники оставлялись без изменений. Позднее BinaryScript, pythonjs
и python-code-display были удалены по отдельному одобрению владельца; их файлы,
история и теги сохранены в этом репозитории. Источники второй группы также были удалены после проверки переноса по разрешению владельца.
Issues, pull requests, releases, stars, настройки и прочие метаданные GitHub не переносились.
Защищённые проекты не изменялись. В этот публичный сборник импортировались только публичные источники.

## Вторая группа: Brainlet, brainfuck, lisp, Lexer

Дата снимка: 14 ноября 2026 года. Перед переносом подтверждена публичная видимость
каждого из четырёх источников и проверены все их ветки и теги.

| Источник | Каталог | Ветка | Исходная вершина | Коммитов | Файлов |
| --- | --- | --- | --- | ---: | ---: |
| [brainlet](https://github.com/paulcodeman/Brainlet) | `brainlet/` | `main` | `1a77311e5586bbf5f57781375e247c6eda920622` | 6 | 2 |
| [brainfuck](https://github.com/paulcodeman/brainfuck) | `brainfuck/` | `master` | `b3f4ceff8876b24ff1caa8223f5301fcc54f8db9` | 11 | 6 |
| [lisp](https://github.com/paulcodeman/lisp) | `lisp/` | `master` | `4f8711c650a94127d1b165f2a65724ba41bd78ad` | 30 | 15 |
| [lexer](https://github.com/paulcodeman/Lexer) | `lexer/` | `master` | `8c48521a499ce9d5f79af91efaf4ccd5faa6aac4` | 18 | 4 |

В каждом источнике только указанная ветка; тегов нет. Дополнительной истории вне
default branch не обнаружено. Исходные вершины сохранены тегами:

- `source/brainlet/main` → `1a77311e5586bbf5f57781375e247c6eda920622`
- `source/brainfuck/master` → `b3f4ceff8876b24ff1caa8223f5301fcc54f8db9`
- `source/lisp/master` → `4f8711c650a94127d1b165f2a65724ba41bd78ad`
- `source/lexer/master` → `8c48521a499ce9d5f79af91efaf4ccd5faa6aac4`

Перенос выполнен через `git subtree add` без `--squash` и переписывания истории.
Все 65 исходных коммитов включены в граф `main` с прежними SHA, авторами,
коммитерами, датами, сообщениями, родителями и деревьями. Сырые объекты коммитов
побайтово сравнены с источниками; проверена достижимость каждого из `main`.
В старых коммитах остаются исходные пути; подпапки появляются в коммитах импорта.

Все 27 текущих исходных файлов (включая README) сохранены побайтово.
Проверено равенство деревьев каждой подпапки исходной ветке: пути, режимы и blob SHA.
Файлы лицензий не обнаружены; новые лицензии не добавлялись. Код не запускался,
его работоспособность не подтверждалась и ошибки не исправлялись.
Ограничения Brainlet, brainfuck, Lisp и Lexer перечислены в общем README.

Первая группа не изменена: сохранены её 21 файл, 20 исходных коммитов и три тега.
Итого семь проектов, 48 исходных файлов и 85 исходных коммитов, плюс два общих
документа и новые коммиты объединения/документации.

### Проверка второй группы

```sh
git rev-parse source/brainlet/main^{tree} main:brainlet
git merge-base --is-ancestor 1a77311e5586bbf5f57781375e247c6eda920622 main
git rev-list --count source/brainlet/main
git rev-parse source/brainfuck/master^{tree} main:brainfuck
git merge-base --is-ancestor b3f4ceff8876b24ff1caa8223f5301fcc54f8db9 main
git rev-list --count source/brainfuck/master
git rev-parse source/lisp/master^{tree} main:lisp
git merge-base --is-ancestor 4f8711c650a94127d1b165f2a65724ba41bd78ad main
git rev-list --count source/lisp/master
git rev-parse source/lexer/master^{tree} main:lexer
git merge-base --is-ancestor 8c48521a499ce9d5f79af91efaf4ccd5faa6aac4 main
git rev-list --count source/lexer/master
git fsck --full
```

Хеши дерева в каждой паре должны совпасть. Проверка ancestry завершается с кодом 0;
числа коммитов должны совпасть с таблицей. Повторная проверка публикации выполняется
в отдельном свежем клоне с GitHub с побайтовым сравнением всех 48 исходных файлов,
объектов 85 исходных коммитов и сохранённых тегов.

## Последующее объединение небольших решений

Дата снимка: 14 ноября 2026 года. Перед импортом проверены видимость, все ветки и теги источников,
состояние назначения и отсутствие локальных изменений. Публикация выполняется без force push.

| Источник | Каталог | Ветка | Исходная вершина | Коммитов | Файлов |
| --- | --- | --- | --- | ---: | ---: |
| [ascii](https://github.com/paulcodeman/ascii) | `ascii/` | `master` | `a2da6d873e8c3977b076cd5f0107f5f56ed5577d` | 4 | 2 |
| [getQuerySelector](https://github.com/paulcodeman/getQuerySelector) | `get-query-selector/` | `master` | `93383b60492d582a1b1609e0e6633a9d2ee1b174` | 1 | 1 |
| [objectToXML](https://github.com/paulcodeman/objectToXML) | `object-to-xml/` | `master` | `16eb4d8b3b22f6ebd58d5e3ec888be4b806a63ff` | 3 | 2 |
| [blockly-php](https://github.com/paulcodeman/blockly-php) | `blockly-php/` | `main` | `b414b4bc52bfbfbbaeb375dd2cd0b5df13261004` | 5 | 2 |
| [php](https://github.com/paulcodeman/php) | `html-to-array/` | `main` | `3de021132bd5571b49ed783520529933daa0dde0` | 6 | 2 |
| [php-dom](https://github.com/paulcodeman/php-dom) | `php-dom/` | `main` | `34bc2aee034377ae91624261d1f2bd6c8ad57802` | 4 | 3 |
| [gcc](https://github.com/paulcodeman/gcc) | `gcc-kolibrios/` | `main` | `ebcea99318a58fe86c91cc4a8a6f5a3e0509b41a` | 2 | 6 |
| [html-application-kolibrios](https://github.com/paulcodeman/html-application-kolibrios) | `html-application-kolibrios/` | `main` | `d496abe133f9fa9ebdbecf9d1eabcf7b2ba01529` | 2 | 2 |
| [console](https://github.com/paulcodeman/console) | `console/` | `master` | `a71eef2a1ec39c0184eeeee7f00a05821a9d6da3` | 99 | 4 |
| [class](https://github.com/paulcodeman/class) | `class/` | `main` | `a21c25fc2df3c85bed09372b5a7f57ae439105c6` | 11 | 8 |

У каждого нового источника одна указанная ветка; тегов и дополнительной истории вне неё нет.
Каждая исходная ветка сохранена тегом `source/<каталог>/<ветка>`. Исходные коммиты
включены без squash или переписывания: SHA, авторы, коммитеры, даты, сообщения, родители
и деревья совпадают побайтово. Исторические пути не переписывались.

В этой группе сохранено 32 файлов и 137 исходных коммитов.
Итого в сборнике 17 проектов, 80 исходных файлов и 222 исходных коммитов.
Проверка после публикации выполняется свежим клонированием: побайтовое совпадение всех
файлов, равенство деревьев (пути, режимы, blob SHA), сырых объектов всех исходных коммитов,
достижимости из сохранённых refs, прежних и новых тегов; `git fsck --full` и чистота checkout.

Перед удалением каждого источника повторно проверяются его видимость, ветки/теги и отсутствие
issues/PR (включая закрытые), релизов, wiki, Pages, развёртываний, окружений, артефактов
и включённых Discussions, требующих отдельного сохранения. У новых источников таких данных
не обнаружено. Исходники удаляются только после проверки опубликованной копии.

Код, бинарники и сетевые интеграции не запускались. Ограничения перечислены в README.
Исходные уведомления и README сохранены; общая лицензия не вводилась.

### Проверка новых снимков

```sh
git rev-parse source/ascii/master^{tree} main:ascii
git merge-base --is-ancestor a2da6d873e8c3977b076cd5f0107f5f56ed5577d main
git rev-parse source/get-query-selector/master^{tree} main:get-query-selector
git merge-base --is-ancestor 93383b60492d582a1b1609e0e6633a9d2ee1b174 main
git rev-parse source/object-to-xml/master^{tree} main:object-to-xml
git merge-base --is-ancestor 16eb4d8b3b22f6ebd58d5e3ec888be4b806a63ff main
git rev-parse source/blockly-php/main^{tree} main:blockly-php
git merge-base --is-ancestor b414b4bc52bfbfbbaeb375dd2cd0b5df13261004 main
git rev-parse source/html-to-array/main^{tree} main:html-to-array
git merge-base --is-ancestor 3de021132bd5571b49ed783520529933daa0dde0 main
git rev-parse source/php-dom/main^{tree} main:php-dom
git merge-base --is-ancestor 34bc2aee034377ae91624261d1f2bd6c8ad57802 main
git rev-parse source/gcc-kolibrios/main^{tree} main:gcc-kolibrios
git merge-base --is-ancestor ebcea99318a58fe86c91cc4a8a6f5a3e0509b41a main
git rev-parse source/html-application-kolibrios/main^{tree} main:html-application-kolibrios
git merge-base --is-ancestor d496abe133f9fa9ebdbecf9d1eabcf7b2ba01529 main
git rev-parse source/console/master^{tree} main:console
git merge-base --is-ancestor a71eef2a1ec39c0184eeeee7f00a05821a9d6da3 main
git rev-parse source/class/main^{tree} main:class
git merge-base --is-ancestor a21c25fc2df3c85bed09372b5a7f57ae439105c6 main
git fsck --full
```

В каждой паре должны совпасть хеши дерева; проверка ancestry завершается с кодом 0.
