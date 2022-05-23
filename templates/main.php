            <section class="content__side">
                <h2 class="content__side-heading">Проекты</h2>

                <nav class="main-navigation">
                    <ul class="main-navigation__list">
                        <?php foreach($projects as $project) :?>
                            <li class="main-navigation__list-item <?= ($project['id'] === (int)filter_input(INPUT_GET, 'project_id')) ? 'main-navigation__list-item--active' : '' ?>">
                                <a class="main-navigation__list-item-link" href="/?project_id=<?= $project['id']; ?>"><?=strip_tags($project['name']); ?></a>
                                <span class="main-navigation__list-item-count"><?= tasks_count($connection, $project['id'], $_SESSION['id']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <a class="button button--transparent button--plus content__side-button"
                   href="/add-project.php" target="project_add">Добавить проект</a>
            </section>

            <main class="content__main">
                <h2 class="content__main-heading">Список задач</h2>

                <form class="search-form" action="index.php" method="get" autocomplete="off">
                    <input class="search-form__input" type="text" name="q" value="" placeholder="Поиск по задачам">

                    <input class="search-form__submit" type="submit" name="" value="Искать">
                </form>

                <div class="tasks-controls">
                    <nav class="tasks-switch">
                        <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
                        <a href="/?filter=today" class="tasks-switch__item">Повестка дня</a>
                        <a href="/?filter=tomorrow" class="tasks-switch__item">Завтра</a>
                        <a href="/?filter=overdue" class="tasks-switch__item">Просроченные</a>
                    </nav>

                    <label class="checkbox">
                        <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
                        <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ($show_complete_tasks === 1): ?>checked<?php endif; ?>>
                        <span class="checkbox__text">Показывать выполненные</span>
                    </label>
                </div>

                <table class="tasks">
                    <?php if ($tasks === null): ?>
                        <tr class="tasks__item">
                            <span>Ничего не найдено по вашему запросу</span>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                        <?php if ($task['done'] && $show_complete_tasks === 0): continue; ?><?php endif ?>
                        <tr class="tasks__item <?= (task_important($task['date_done']) && !$task['done']) ? 'task--important' : 'task' ?> <?= ($task['done']) ? 'task--completed' : '' ?>">
                            <td class="task__select">
                                <label class="checkbox task__checkbox">
                                    <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="<?= $task['id'] ?>">
                                    <span class="checkbox__text"><?=strip_tags($task['name']);?></span>
                                </label>
                            </td>

                            <?php if ($task['file']): ?>
                                <td class="task__file"><a class="download-link" href="<?= $task['file'] ?>"><?= substr_replace($task['file'], '', 0, 9) ?></a></td>
                            <?php else: ?>
                                <td class="task__file"></td>
                            <?php endif; ?>

                            <td class="task__date"><?= $task['date_done']; ?></td>

                            <td class="task__controls"></td>

                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </main>
