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
            <h2 class="content__main-heading">Добавление проекта</h2>

            <form class="form"  action="" method="post" autocomplete="off">
            <div class="form__row">
                <label class="form__label" for="project_name">Название <sup>*</sup></label>

                <input class="form__input <?= (isset($errors['project_name'])) ? 'form__input--error' : '' ?>" type="text" name="project_name" id="project_name" value="<?= (isset($errors)) ? get_post_val('project_name') : '' ?>" placeholder="Введите название проекта">
                <?php if (isset($errors['project_name'])): ?>
                    <p class="form__message"><?= $errors['project_name'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form__row form__row--controls">
                <input class="button" type="submit" name="" value="Добавить">
            </div>
            </form>
        </main>
