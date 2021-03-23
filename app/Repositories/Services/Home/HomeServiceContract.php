<?php
// set namspace of file contract home service
namespace App\Repositories\Services\Home;

interface HomeServiceContract {
    /**
     * class homservicerContract 
     * define function use in homservice connect with Controller.
     * @author Dat
     * date: 2019/10/03
     */
    public function turnOver();
    public function rankingOrder($request);
}