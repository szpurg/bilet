<?php require 'header.php'; ?>
<h1>Ustawienia</h1>

<form action="/panel/settings" method="post">
<?php foreach($this->definition as $field => $settingDef):?>
    <div>
    <?php switch($settingDef['type']) {
            case 'text':
            case 'password':
                ?>
                <label> <?php print $settingDef['label']?>
                    <input type="<?php print $settingDef['type']?>" name="<?php print $field?>" value="<?php print settings::getInstance()->get($field)?>" />
                </label>
                <?php
                break;
            case 'select':
                ?>
                <label> <?php print $settingDef['label']?>
                    <select name="<?php print $field?>">
                        <?php foreach($settingDef['choices'] as $value => $choice):?>
                            <option value="<?php print $value?>"<?php print settings::get($field) == $value ? ' selected="1"' : ''?>><?php print $choice?></option>
                        <?php endforeach;?>
                    </select>
                </label>
                <?php
                break;
        }
    ?>
    </div>


<?php endforeach ?>
    <div>
        <input value="Zapisz" name="save" type="submit">
        <input value="Powrót" name="back" type="submit">    
    </div>
</form>

<?php require 'footer.php'; ?>

