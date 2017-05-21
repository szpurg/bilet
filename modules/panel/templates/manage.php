<?php require 'header.php'; ?>

<h1>Wydarzenie "<?php print $this->event->getName() ?>"</h1>
<form action="<?php print MODULE_URI ?>saveEvent/<?php print $this->event->getIdentifier(true) . '/' . $this->index?>" method="post">
    <div id="columns">
        <div id="sectors" class="column container">
            <label><h2><input type="checkbox" class="checkall"> Sektory</h2></label>
            <?php if ($this->sectors): ?>
                <?php foreach ($this->sectors as $sector): ?>
                    <div>
                        <label><input type="checkbox" name="sectors[]"<?php print in_array($sector['name'], $this->event->getSectors() ? $this->event->getSectors() : array()) ? ' checked="1"' : ''?>value="<?php print $sector['name'] ?>" /><?php print $sector['name'] . " (" . $sector['available'] . ")" ?></label>
                    </div>
                <?php endforeach; ?>
            <?php endif ?>
        </div>
        <div id="users" class="column container">
            <label><h2><input type="checkbox" class="checkall"> Konta</h2></label>
            <?php if ($this->users): ?>
                <?php foreach ($this->users as $user): ?>
                    <div class="userContainer">
                        <label>
                            <input type="checkbox" name="users[]"<?php print in_array($user->getLogin(), $this->event->getUsers()) ? ' checked="1"' : ''?>value="<?php print $user->getLogin()?>" /><?php print $user->getLogin() ?>
                        </label>
                        <a class="remove account" href="<?php print MODULE_URI?>deleteAccount/<?php print $user->getLogin()?>?returner=manage/<?php print $this->event->getIdentifier(true) . "/" . $this->index?>">[usuń]</a><?php $user->getCaptchaNeeded() ? print '&nbsp<a class="captchaActivator" href="/cccc?cuser=' . $user->getLogin() . '&capac" target="_blank"><span class="warning">[c]</span></a>'
                                : null?>
                        <?php print $user->getBasketCount() ? '&nbsp;<a href="/index.php?f78=f97&cuser=' . $user->getLogin() . '" target="_blank" class="basket">[' . $user->getBasketCount() . ']</a>' : ''?>
                    </div>
                <?php endforeach; ?>
            <?php endif ?>
        </div>
        <div id="settings" class="column container">
            <label><h2>Ustawienia</h2></label>
            <input type="hidden" name="settings[]" />
            <div>
                <label><input type="checkbox" name="settings[active]" value="1" <?php print $this->event->getSetting('active') ? ' checked="1"' : ''?>/> Uruchomione</label>
            </div>
            <div>
                <label><input type="checkbox" name="settings[turbo]" value="1" <?php print $this->event->getSetting('turbo') ? ' checked="1"' : ''?>/> Turbo</label>
            </div>
            <div>
                <label><input type="checkbox" name="settings[reverseBuy]" value="1" <?php print $this->event->getSetting('reverseBuy') ? ' checked="1"' : ''?>/> Kupuj od dołu</label>
            </div>
            <div>
                <label>Minimalna liczba miejsc w sektorze:</label>
                <select name="settings[minPlaces]">
                    <option value='0'>Bez limitu</option>
                    <?php for ($i = 2; $i <= 10; $i++):?>
                    <option value='<?php print $i?>' <?php print $this->event->getSetting('minPlaces') == $i ? " selected='1'" : ""?>><?php print $i?></option>
                    <?php endfor ?>
                </select>
            </div>
        </div>
    </div>
    <div style="clear: both"></div>
    <div>
        <input type="submit" value="Zapisz" name="save" />
        <input type="submit" value="Powrót" name="back" />
    </div>
</form>
    
<div class="columnspan">
    <form action="<?php print MODULE_URI ?>saveAccount" method="post">
        <label>Dodaj konto</label>
        <label>Login: <input type="text" name="account[login]" /></label>
        <label>Hasło: <input type="text" name="account[password]" /></label>
        <input type="hidden" name="returner" value="manage/<?php print $this->event->getIdentifier(true) . "/" . $this->index?>" />
        <input type="submit" name="saveAccount" value="Dodaj" />
    </form>
</div>
<script type="text/javascript">
sectorsActions.init();
</script>    
<?php
require 'footer.php';
