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
            <th>Status</th>
            <th>Funkcje</th>
        </tr>
    <?php foreach($this->events as $eventArray):?>
        <?php foreach ($eventArray as $index => $event):?>
            <tr>
                <td><a href="<?php print $this->getUri() . 'manage/' . $event->getIdentifier(true) . '/' . $index?>"><?php print $event->getIdentifier()?></a></td>
                <td><?php print $event->getName()?></td>
                <td><?php print $event->getUrl()?></td>
                <td class="status"><?php print ($event->getActive() ? 
                            'Uruchomione' : 'Nieaktywne') 
                            . '<br>(wybrane&nbsp;sektory:&nbsp' 
                            . count($event->getSectors()) 
                            . ')<br>(konta:&nbsp' . count($event->getUsers()) . ')' 
                            . ($event->getTurbo() ? '<br><span class="turbo">(turbo)</span>' : '<br>(normalny)') 
                            . ($event->getSetting('reverseBuy') ? '<br>(od&nbsp;dołu)' : '') 
                            ?>
                </td>
                <td>
                    <div>
                        <a href="<?php print $this->getUri() . 'manage/' . $event->getIdentifier(true) . '/' . $index?>">
                            [ZARZĄDZAJ]
                        </a>
                    </div>
                    <div>
                        <a href="<?php print $this->getUri() . 'switchEvent/' . $event->getIdentifier(true) . '/' . $index?>">
                            [<?php print $event->getActive() ? 'ZATRZYMAJ' : 'URUCHOM'?>]
                        </a>
                    </div>
                    <div>
                        <a class="remove" href="<?php print $this->getUri() . 'removeEvent/' . $event->getIdentifier(true) . '/' . $index?>">
                            [USUŃ]
                        </a>
                    </div>
                    <div>
                        <a href="<?php print $this->getUri() . 'editEvent/' . $event->getIdentifier(true) . '/' . $index?>">
                            [ZMIEŃ&nbsp;DANE]
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach;?>
    <?php endforeach;?>
    </table>
<?php else:?>
    Brak zdefiniowanych wydarzeń
<?php endif;?>



    
<?php require 'footer.php';