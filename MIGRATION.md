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
история и теги сохранены в этом репозитории. Источники второй группы не изменялись и не удалялись.
Issues, pull requests, releases, stars, настройки и прочие метаданные GitHub не переносились.
Tagix, TagixTech, приватные репозитории и любые другие источники не затрагивались.

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
