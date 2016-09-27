<?php require 'header.php'; ?>
<h1>Wydarzenia</h1>
<div class="func">
    <a href="<?php print $this->getUri()?>newEvent">[Dodaj wydarzenie]</a>
</div>

<?php if ($this->events):?>
    <table>
        <tr>
            <th>Identyfikator</th>
            <th>Nazwa</th>
            <th>URL</th>
            <th>Funkcje</th>
        </tr>
    <?php foreach($this->events as $event):?>
        <tr>
            <td><?php print $event['identifier']?></td>
            <td><?php print $event['name']?></td>
            <td><?php print $event['url']?></td>
            <td><a href="<?php print $this->getUri() . 'manage/' . $event['identifier']?>">[ZARZĄDZAJ]</a></td>
        </tr>
    <?php endforeach;?>
    </table>
<?php else:?>
    Brak zdefiniowanych wydarzeń
<?php endif;?>



    
<?php require 'footer.php';