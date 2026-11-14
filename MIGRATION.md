# Проверка переноса

Дата снимка: 14 ноября 2026 года.

## Исходные снимки

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

Максимальный размер исторического blob: 3 303 064 байта.
Бинарники сохранены как обычные Git-объекты, без LFS. Они не запускались.
Функциональные тесты и аудит безопасности, совместимости, происхождения бинарников
или полноты прав на распространение не проводились. Известные ограничения перечислены в README.

## Границы переноса

Исходные репозитории оставлены без изменений и не удалены.
Issues, pull requests, releases, stars, настройки и прочие метаданные GitHub не переносились.
Tagix, TagixTech и другие репозитории не затрагивались.
