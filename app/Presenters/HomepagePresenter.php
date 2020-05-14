<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;
use Nette\Application\UI\Form   ;


class HomepagePresenter extends Nette\Application\UI\Presenter
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }
    
    public function renderDefault($order = 'id')
    {
        Debugger::barDump($order);
        $this->template->knihy = $this->database
                                       ->table('item')
                                       ->order($order)
                                       ->fetchAll();
    }
    
    public function renderKniha($id = 'id') {
        $this->template->kniha = $this->database
                                          ->table('item')
                                          ->get($id);
    }
    
    
    public function renderUpdate($id) {
        $data = $this->database->table('item')
                               ->get($id);
        $data = $data->toArray();
        Debugger::barDump($data);
        $this['knihaForm']->setDefaults($data);
    }
    
    public function actionDelete($id) { 
        $row = $this->database->table('item')
                              ->get($id);
        if ($row->delete()) {
            $this->flashMessage('Záznam byl úspěšně smazán');
        } else {
            $this->flashMessage('Záznam nemohl být smazán');
        }
        $this->redirect("default");
    } 
    
    protected function createComponentKnihaForm()
    {
        $form = new Form;
        $form->addText('title', 'Název díla')
                        ->setRequired(true);
        $form->addText('author', 'Autor')
                        ->addRule(Form::MAX_LENGTH, 'Jméno autora nesmí být delší než 50 znaků!', 50)
                        ->setRequired(true);
        $form->addTextArea('anotation', 'Charakteristika díla')
                        ->setHtmlAttribute('rows', '10');
        $form->addInteger('year', 'Rok vzniku')
                        ->setDefaultValue(2000)
                        ->addRule(Form::MAX_LENGTH, 'Zadejte platný rok', 4);
        $category = [
            'drama' => 'drama',
            'poezie' => 'poezie',
            'próza' => 'próza',
        ];
        $form->addRadioList('category', 'Literární druh', $category); 
        $stars = [
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        ];
        $form->addSelect('stars','Hodnocení', $stars);

        $form->addSubmit('submit', 'Potvrdit');
        $form->onSuccess[] = [$this, 'KnihaFormSucceeded'];
        return $form;
    }

    public function actionInsert(){
        $this['knihaForm']['title'];
        $this['knihaForm']['author'];
        $this['knihaForm']['anotation'];
        $this['knihaForm']['year'];
        $this['knihaForm']['category']->setDefaultValue('próza');
        $this['knihaForm']['stars'];
    }

    // volá se po úspěšném odeslání formuláře
    public function knihaFormSucceeded(Form $form, array $values){
        if ($id=$this->getParameter('id')) {
            $this->database->table('item')
                    ->get($id)
                    ->update((array)$values);
            $this->flashMessage('Záznam byl aktualizován.');
        } else {
            $this->database->table('item')->insert((array)$values);
            $this->flashMessage('Byl vložen nový záznam.');            
        }            
        $this->redirect('Homepage:');
    }   
}