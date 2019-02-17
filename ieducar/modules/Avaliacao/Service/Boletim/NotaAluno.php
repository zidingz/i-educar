<?php

trait Avaliacao_Service_Boletim_NotaAluno
{
    /**
     * Uma instância de Avaliacao_Model_NotaAluno, que é a entrada que contém
     * o cruzamento de matrícula com as notas do aluno nos diversos componentes
     * cursados.
     *
     * @var Avaliacao_Model_NotaAluno
     */
    protected $_notaAluno;

    /**
     * Getter.
     *
     * @return Avaliacao_Model_NotaAluno|NULL
     */
    protected function _getNotaAluno()
    {
        if (!is_null($this->_notaAluno)) {
            return $this->_notaAluno;
        }

        $notaAluno = $this->getNotaAlunoDataMapper()->findAll(
            [],
            ['matricula' => $this->getOption('matricula')]
        );

        if (0 == count($notaAluno)) {
            return null;
        }

        $this->_setNotaAluno($notaAluno[0]);

        return $this->_notaAluno;
    }

    /**
     * Setter.
     *
     * @param Avaliacao_Model_NotaAluno $nota
     *
     * @return $this
     */
    protected function _setNotaAluno(Avaliacao_Model_NotaAluno $nota)
    {
        $this->_notaAluno = $nota;

        return $this;
    }
}
