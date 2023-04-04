<?php

namespace App\modeles;

class FilterVille
{
    private ?string $keyWord = null;

    /**
     * @return string|null
     */
    public function getKeyWord(): ?string
    {
        return $this->keyWord;
    }

    /**
     * @param string|null $keyWord
     */
    public function setKeyWord(?string $keyWord): void
    {
        $this->keyWord = $keyWord;
    }


}