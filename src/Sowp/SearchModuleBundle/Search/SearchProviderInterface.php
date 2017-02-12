<?php

namespace Sowp\SearchModuleBundle\Search;

interface SearchProviderInterface
{
    public function search($query);
    public function getTypeName();
//    public function getResultsMulti();
    public function getResultsSingle();
    public function getQbSingle();
    public function getLink();
//    public function getQbMulti();
    //public function setTypeName($name);
}