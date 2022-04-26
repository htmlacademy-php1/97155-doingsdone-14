            <section class="content__side">
                <h2 class="content__side-heading">Проекты</h2>

                <nav class="main-navigation">
                    <ul class="main-navigation__list">
                        <?php foreach($projects as $project) :?>
                            <li class="main-navigation__list-item <?= ($project['id'] === (int)filter_input(INPUT_GET, 'project_id')) ? 'main-navigation__list-item--active' : '' ?>">
                                <a class="main-navigation__list-item-link" href="/?project_id=<?= $project['id']; ?>"><?=strip_tags($project['name']); ?></a>
                                <span class="main-navigation__list-item-count"><?= tasks_count($connection, $project['id']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <a class="button button--transparent button--plus content__side-button"
                   href="pages/form-project.html" target="project_add">Добавить проект</a>
            </section>

            <main class="content__main">
            <h2 class="content__main-heading">Добавление задачи</h2>

            <form class="form"  action="" method="post" autocomplete="off" enctype="multipart/form-data">
            <div class="form__row">
                <label class="form__label" for="name">Название <sup>*</sup></label>

                <input class="form__input" type="text" name="name" id="name" value="" placeholder="Введите название">
            </div>

            <div class="form__row">
                <label class="form__label" for="project">Проект <sup>*</sup></label>

                <select class="form__input form__input--select" name="project_id" id="project">
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id']; ?>"><?= $project['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__row">
                <label class="form__label" for="date">Дата выполнения</label>

                <input class="form__input form__input--date" type="text" name="date_done" id="date" value="" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
            </div>

            <div class="form__row">
                <label class="form__label" for="file">Файл</label>

                <div class="form__input-file">
                <input class="visually-hidden" type="file" name="file" id="file" value="">

                <label class="button button--transparent" for="file">
                    <span>Выберите файл</span>
                </label>
                </div>
            </div>

            <div class="form__row form__row--controls">
                <input class="button" type="submit" name="" value="Добавить">
            </div>
            </form>
        </main>
