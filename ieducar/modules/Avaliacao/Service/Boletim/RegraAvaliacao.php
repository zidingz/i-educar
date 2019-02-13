<?php

trait Avaliacao_Service_Boletim_RegraAvaliacao
{
    /**
     * Instância da regra de avaliação, com o qual o serviço irá utilizar para
     * decidir o fluxo da lógica.
     *
     * @var RegraAvaliacao_Model_Regra
     */
    protected $_regra;

    /**
     * @return RegraAvaliacao_Model_Regra
     */
    public function getRegraAvaliacao()
    {
        return $this->_regra;
    }

    /**
     * @deprecated
     *
     * @see Avaliacao_Service_Boletim_RegraAvaliacao::getRegraAvaliacao()
     *
     * @return RegraAvaliacao_Model_Regra
     */
    public function getRegra()
    {
        return $this->getRegraAvaliacao();
    }

    /**
     * @param RegraAvaliacao_Model_Regra $regra
     *
     * @return $this
     */
    protected function _setRegra(RegraAvaliacao_Model_Regra $regra)
    {
        $this->_regra = $regra;

        return $this;
    }

    /**
     * Retorna o tipo de presença da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoPresenca()
    {
        return $this->getRegraAvaliacao()->get('tipoPresenca');
    }

    /**
     * Retorna o tipo de parecer descritivo da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoParecerDescritivo()
    {
        return $this->getRegraAvaliacao()->get('parecerDescritivo');
    }

    /**
     * @return TabelaArredondamento_Model_Tabela
     */
    public function getRegraAvaliacaoTabelaArredondamento()
    {
        return $this->getRegraAvaliacao()->tabelaArredondamento;
    }

    /**
     * Retorna o tipo de nota da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoNota()
    {
        return $this->getRegraAvaliacao()->get('tipoNota');
    }

    /**
     * Retorna o tipo de progressão da regra de avaliação.
     *
     * @return int
     */
    public function getRegraAvaliacaoTipoProgressao()
    {
        return $this->getRegraAvaliacao()->get('tipoProgressao');
    }

    /**
     * Retorna "1" se a regra de avaliação é do tipo nota geral por etapa.
     *
     * @return string
     */
    public function getRegraAvaliacaoNotaGeralPorEtapa()
    {
        return $this->getRegraAvaliacao()->get('notaGeralPorEtapa');
    }

    /**
     * Indica se a regra de avaliação possui recuperação final.
     *
     * @return bool
     */
    public function hasRegraAvaliacaoFormulaRecuperacao()
    {
        return ! is_null($this->getRegraAvaliacao()->get('formulaRecuperacao'));
    }

    /**
     * Indica se a regra de avaliação possui fórmula para calcular a média da
     * recuperação.
     *
     * @return bool
     */
    public function hasRegraAvaliacaoMediaRecuperacao()
    {
        return boolval($this->getRegraAvaliacao()->get('mediaRecuperacao'));
    }
}
