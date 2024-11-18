<?php

namespace HercegDoo\AIComposePlugin\Utilities;

class TemplateObjectFiller
{

    public function createSelectField($attrib, $options_key,  $name){

        $defaultValue = "default".ucfirst($options_key);
        $defaultValue = substr($defaultValue, 0, -1);

        $options = \rcmail::get_instance()->output->get_env('aiPluginOptions')[$options_key];
        $defaultOption = \rcmail::get_instance()->output->get_env('aiPluginOptions')[$defaultValue];

        // Kreiranje atributa za select element
        $attrib = ['name' => $name];

        // Kreiramo instancu select elementa koristeći \html_select klasu
        $selector = new \html_select($attrib);

        // Dodajemo opcije u select polje: ključevi su prikazani korisnicima, a vrednosti su one koje se šalju
        $selector->add(array_values($options), array_keys($options));

        // Ako postoji postovana vrednost, koristićemo je, inače postavljamo podrazumevanu vrednost
        if (isset($_POST[$name])) {
            $sel = $_POST[$name];
        } else {
            $sel = $defaultOption; // Podrazumevana vrednost
        }

        // Generisanje HTML izlaza za select polje
        // Prikazujemo select sa trenutno selektovanom vrednošću

        return $selector->show($sel);
    }

}