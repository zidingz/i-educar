<?php

use App\User;
use Illuminate\Support\Facades\Auth;

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/clsPmieducarUsuario.inc.php';
require_once 'include/pmieducar/clsPmieducarEscolaUsuario.inc.php';
require_once 'include/modules/clsModulesAuditoriaGeral.inc.php';
require_once 'include/pmieducar/clsPmieducarFuncionarioVinculo.inc.php';

class clsIndexBase extends clsBase
{
    public function Formular()
    {
        $this->SetTitulo('Cadastro de usuários');
        $this->processoAp = 555;
    }
}

class indice extends clsCadastro
{
    public $ref_pessoa;
    public $ref_cod_setor_new;

    //dados do funcionario
    public $nome;
    public $matricula;
    public $_senha;
    public $ativo;
    public $ref_cod_funcionario_vinculo;
    public $proibido;
    public $matricula_permanente;
    public $matricula_interna;
    public $escola;

    //senha carregada do banco (controle de criptografia)
    public $confere_senha;

    //setor e subsetores
    public $setor_0;
    public $setor_1;
    public $setor_2;
    public $setor_3;
    public $setor_4;

    public function Inicializar()
    {
        $retorno = 'Novo';

        $this->ref_pessoa = $_POST['ref_pessoa'];

        if ($_GET['ref_pessoa']) {
            $this->ref_pessoa = $_GET['ref_pessoa'];
        }

        if (is_numeric($this->ref_pessoa)) {
            $obj_funcionario = new clsPortalFuncionario($this->ref_pessoa);
            $det_funcionario = $obj_funcionario->detalhe();

            if ($det_funcionario) {
                foreach ($det_funcionario as $campo => $valor) {
                    $this->$campo = $valor;
                }

                $this->_senha = $this->senha;
                $this->confere_senha = $this->_senha;
                $this->fexcluir = true;
                $retorno = 'Editar';
            }

            $this->status = $this->ativo;

            $obj = new clsPmieducarUsuario($this->ref_pessoa);

            $registro = $obj->detalhe();

            if ($registro) {
                foreach ($registro as $campo => $val) {
                    $this->$campo = $val;
                }

                $obj_permissoes = new clsPermissoes();
                $this->fexcluir = $obj_permissoes->permissao_excluir(555, $this->pessoa_logada, 7, 'educar_usuario_lst.php', true);
                $retorno = 'Editar';
            }
        }

        $this->url_cancelar = $retorno == 'Editar'
            ? "educar_usuario_det.php?ref_pessoa={$this->ref_pessoa}"
            : 'educar_usuario_lst.php';

        $this->nome_url_cancelar = 'Cancelar';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb($nomeMenu . ' usuário', [
            url('intranet/educar_configuracoes_index.php') => 'Configurações',
        ]);

        return $retorno;
    }

    public function Gerar()
    {
        $obj_permissao = new clsPermissoes();

        $this->campoOculto('ref_pessoa', $this->ref_pessoa);

        if (is_numeric($this->ref_pessoa)) {
            $this->campoOculto('confere_senha', $this->confere_senha);
        }

        if ($_POST) {
            foreach ($_POST as $campo => $val) {
                $this->$campo = ($this->$campo) ? $this->$campo : $val;
            }
        }

        $this->ref_cod_setor_new = 0;

        if (!$this->ref_cod_setor_new && is_numeric($this->ref_pessoa)) {
            $objFuncionario = new clsPortalFuncionario($this->ref_pessoa);
            $detFunc = $objFuncionario->detalhe();
            $this->ref_cod_setor_new = $detFunc['ref_cod_setor_new'];
        }

        if ($_GET['ref_pessoa']) {
            $obj_funcionario = new clsPessoaFj($this->ref_pessoa);
            $det_funcionario = $obj_funcionario->detalhe();

            $this->nome = $det_funcionario['nome'];

            $this->campoRotulo('nome', 'Nome', $this->nome);
        } else {
            $parametros = new clsParametrosPesquisas();
            $parametros->setSubmit(1);
            $parametros->setPessoa('F');
            $parametros->setPessoaNovo('S');
            $parametros->setPessoaEditar('N');
            $parametros->setPessoaTela('frame');
            $parametros->setPessoaCPF('N');
            $parametros->adicionaCampoTexto('nome', 'nome');
            $parametros->adicionaCampoTexto('nome_busca', 'nome');
            $parametros->adicionaCampoTexto('ref_pessoa', 'idpes');
            $this->campoTextoPesquisa('nome_busca', 'Nome', $this->nome, 30, 255, true, 'pesquisa_pessoa_lst.php', false, false, '', '', $parametros->serializaCampos() . '&busca=S', true);
            $this->campoOculto('nome', $this->nome);
            $this->campoOculto('ref_pessoa', $this->ref_pessoa);
        }

        $this->campoTexto('matricula', 'Matrícula', $this->matricula, 12, 12, true);
        $this->campoSenha('_senha', 'Senha', $this->_senha, true);
        $this->campoEmail('email', 'E-mail usuário', $this->email, 50, 50, false, false, false, 'Utilizado para redefinir a senha, caso o usúario esqueça<br />Este campo pode ser gravado em branco, neste caso será solicitado um e-mail ao usuário, após entrar no sistema.');
        $this->campoTexto('matricula_interna', 'Matrícula interna', $this->matricula_interna, 30, 30, false, false, false, 'Utilizado somente para registro, caso a instituição deseje que a matrícula interna deste funcionário seja registrada no sistema.');

        $opcoes = [0 => 'Inativo', 1 => 'Ativo'];

        if (!$this->ref_cod_pessoa_fj == '') {
            $this->campoLista('ativo', 'Status', $opcoes, $this->status);
        } else {
            $this->campoLista('ativo', 'Status', $opcoes, 1);
        }

        $objFuncionarioVinculo = new clsPmieducarFuncionarioVinculo;
        $opcoes = ['' => 'Selecione'] + $objFuncionarioVinculo->lista();
        $this->campoLista('ref_cod_funcionario_vinculo', 'Vínculo', $opcoes, $this->ref_cod_funcionario_vinculo);

        $tempoExpiraSenha = config('legacy.app.user_accounts.default_password_expiration_period');

        if (is_numeric($tempoExpiraSenha)) {
            $this->campoOculto('tempo_expira_senha', $tempoExpiraSenha);
        } else {
            $opcoes = ['' => 'Selecione', 5 => '5', 30 => '30', 60 => '60', 90 => '90', 120 => '120', 180 => '180'];
            $this->campoLista('tempo_expira_senha', 'Dias p/ expirar a senha', $opcoes, $this->tempo_expira_senha);
        }

        $opcoes = [null => 'Não', 1 => 'Sim'];
        $this->campoLista('proibido', 'Banido', $opcoes, $this->proibido, '', false, '', '', false, false);

        $opcoes = [null => 'Não', 1 => 'Sim'];
        $this->campoLista('matricula_permanente', 'Matrícula permanente', $opcoes, $this->matricula_permanente, '', false, '', '', false, false);

        $opcoes = ['' => 'Selecione'];

        $objTemp = new clsPmieducarTipoUsuario();
        $objTemp->setOrderby('nm_tipo ASC');

        /** @var User $user */
        $user = Auth::user();

        // verifica se pessoa logada é super-usuario
        if ($user->isAdmin()) {
            $lista = $objTemp->lista(null, null, null, null, null, null, null, null, 1);
        } else {
            $lista = $objTemp->lista(null, null, null, null, null, null, null, null, 1, $obj_permissao->nivel_acesso($this->pessoa_logada));
        }

        if (is_array($lista) && count($lista)) {
            foreach ($lista as $registro) {
                $opcoes["{$registro['cod_tipo_usuario']}"] = "{$registro['nm_tipo']}";
                $opcoes_["{$registro['cod_tipo_usuario']}"] = "{$registro['nivel']}";
            }
        }

        $tamanho = sizeof($opcoes_);

        echo "<script>\nvar cod_tipo_usuario = new Array({$tamanho});\n";

        foreach ($opcoes_ as $key => $valor) {
            echo "cod_tipo_usuario[{$key}] = {$valor};\n";
        }

        echo '</script>';

        $this->campoLista('ref_cod_tipo_usuario', 'Tipo Usuário', $opcoes, $this->ref_cod_tipo_usuario, '', null, null, null, null, true);

        $nivel = $obj_permissao->nivel_acesso($this->ref_pessoa);

        $this->campoOculto('nivel_usuario_', $nivel);

        $this->inputsHelper()->dynamic(['instituicao']);
        $this->inputsHelper()->multipleSearchEscola(null, [
            'label' => 'Escola(s)',
            'required' => false
        ]);

        $scripts = ['/modules/Cadastro/Assets/Javascripts/Usuario.js'];

        Portabilis_View_Helper_Application::loadJavascript($this, $scripts);

        $this->acao_enviar = 'valida()';
    }

    public function Novo()
    {
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->mensagem = 'Formato do e-mail inválido.';

            return false;
        }

        //setor recebe o id do ultimo subsetor selecionado
        $this->ref_cod_setor_new = 0;

        for ($i = 0; $i < 5; $i++) {
            $nmvar = "setor_{$i}";

            if (is_numeric($this->$nmvar) && $this->$nmvar) {
                $this->ref_cod_setor_new = $this->$nmvar;
            }
        }

        if (!$this->validatesUniquenessOfMatricula($this->ref_pessoa, $this->matricula)) {
            return false;
        }

        if (!$this->validatesPassword($this->matricula, $this->_senha)) {
            return false;
        }

        $obj_funcionario = new clsPortalFuncionario($this->ref_pessoa, $this->matricula, md5($this->_senha), $this->ativo, null, null, null, null, null, null, null, null, null, null, $this->ref_cod_funcionario_vinculo, $this->tempo_expira_senha, null, 'NOW()', 'NOW()', $this->pessoa_logada, empty($this->proibido) ? 0 : 1, $this->ref_cod_setor_new, null, empty($this->matricula_permanente) ? 0 : 1, 1, $this->email, $this->matricula_interna);

        if ($obj_funcionario->cadastra()) {
            $funcionario = $obj_funcionario->detalhe();
            $auditoria = new clsModulesAuditoriaGeral('funcionario', $this->pessoa_logada, $this->ref_pessoa);
            $auditoria->inclusao($funcionario);

            if ($this->ref_cod_instituicao) {
                $obj = new clsPmieducarUsuario($this->ref_pessoa, null, $this->ref_cod_instituicao, $this->pessoa_logada, $this->pessoa_logada, $this->ref_cod_tipo_usuario, null, null, 1);
            } else {
                $obj = new clsPmieducarUsuario($this->ref_pessoa, null, null, $this->pessoa_logada, $this->pessoa_logada, $this->ref_cod_tipo_usuario, null, null, 1);
            }

            if ($obj->existe()) {
                $detalheAntigo = $obj->detalhe();
                $cadastrou = $obj->edita();
                $detalheNovo = $obj->detalhe();
                $auditoria = new clsModulesAuditoriaGeral('usuario', $this->pessoa_logada, $cadastrou);
                $auditoria->alteracao($detalheAntigo, $detalheNovo);
            } else {
                $cadastrou = $obj->cadastra();
                $usuario = new clsPmieducarUsuario($cadastrou);
                $usuario = $usuario->detalhe();
                $auditoria = new clsModulesAuditoriaGeral('usuario', $this->pessoa_logada, $cadastrou);
                $auditoria->inclusao($usuario);
            }

            $this->insereUsuarioEscolas($this->ref_pessoa, $this->escola);

            if ($cadastrou) {
                $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';
                $this->simpleRedirect('educar_usuario_lst.php');
            }
        }

        $this->mensagem = 'Cadastro não realizado.<br>';

        return false;
    }

    public function Editar()
    {
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->mensagem = 'Formato do e-mail inválido.';

            return false;
        }

        $this->ref_cod_setor_new = 0;

        for ($i = 0; $i < 5; $i++) {
            $nmvar = "setor_{$i}";

            if (is_numeric($this->$nmvar) && $this->$nmvar) {
                $this->ref_cod_setor_new = $this->$nmvar;
            }
        }

        if (!$this->validatesUniquenessOfMatricula($this->ref_pessoa, $this->matricula)) {
            return false;
        }

        if (!$this->validatesPassword($this->matricula, $this->_senha)) {
            return false;
        }

        //verifica se a senha ja esta criptografada
        if ($this->_senha != $this->confere_senha) {
            $this->_senha = md5($this->_senha);
        }

        $obj_funcionario = new clsPortalFuncionario($this->ref_pessoa, $this->matricula, $this->_senha, $this->ativo, null, null, null, null, null, null, null, null, null, null, $this->ref_cod_funcionario_vinculo, $this->tempo_expira_senha, null, 'NOW()', 'NOW()', $this->pessoa_logada, empty($this->proibido) ? 0 : 1, $this->ref_cod_setor_new, null, empty($this->matricula_permanente) ? 0 : 1, null, $this->email, $this->matricula_interna);
        $detalheAntigo = $obj_funcionario->detalhe();

        if ($obj_funcionario->edita()) {
            $detalheNovo = $obj_funcionario->detalhe();
            $auditoria = new clsModulesAuditoriaGeral('funcionario', $this->pessoa_logada, $this->ref_pessoa);
            $auditoria->alteracao($detalheAntigo, $detalheNovo);

            if ($this->ref_cod_instituicao) {
                $obj = new clsPmieducarUsuario($this->ref_pessoa, null, $this->ref_cod_instituicao, $this->pessoa_logada, $this->pessoa_logada, $this->ref_cod_tipo_usuario, null, null, 1);
            } else {
                $obj = new clsPmieducarUsuario($this->ref_pessoa, null, null, $this->pessoa_logada, $this->pessoa_logada, $this->ref_cod_tipo_usuario, null, null, 1);
            }

            if ($obj->existe()) {
                $detalheAntigo = $obj->detalhe();
                $editou = $obj->edita();
                $detalheNovo = $obj->detalhe();
                $auditoria = new clsModulesAuditoriaGeral('usuario', $this->pessoa_logada, $editou);
                $auditoria->alteracao($detalheAntigo, $detalheNovo);
            } else {
                $editou = $obj->cadastra();
                $usuario = new clsPmieducarUsuario($editou);
                $usuario = $usuario->detalhe();
                $auditoria = new clsModulesAuditoriaGeral('usuario', $this->pessoa_logada, $editou);
                $auditoria->inclusao($usuario);
            }

            $this->insereUsuarioEscolas($this->ref_pessoa, $this->escola);

            if ($this->nivel_usuario_ == 8) {
                $obj_tipo = new clsPmieducarTipoUsuario($this->ref_cod_tipo_usuario);
                $det_tipo = $obj_tipo->detalhe();
                if ($det_tipo['nivel'] != 8) {
                    $obj_usuario_bib = new clsPmieducarBibliotecaUsuario();
                    $lista_bibliotecas_usuario = $obj_usuario_bib->lista(null, $this->pessoa_logada);

                    if ($lista_bibliotecas_usuario) {
                        foreach ($lista_bibliotecas_usuario as $usuario) {
                            $obj_usuario_bib = new clsPmieducarBibliotecaUsuario($usuario['ref_cod_biblioteca'], $this->pessoa_logada);
                            if (!$obj_usuario_bib->excluir()) {
                                return false;
                            }
                        }
                    }
                }
            }

            if ($this->ref_cod_instituicao != $this->ref_cod_instituicao_) {
                $obj_biblio = new clsPmieducarBiblioteca();
                $lista_biblio_inst = $obj_biblio->lista(null, $this->ref_cod_instituicao_);
                if ($lista_biblio_inst) {
                    foreach ($lista_biblio_inst as $biblioteca) {
                        $obj_usuario_bib = new clsPmieducarBibliotecaUsuario($biblioteca['cod_biblioteca'], $this->pessoa_logada);
                        $obj_usuario_bib->excluir();
                    }
                }
            }

            if ($editou) {
                $this->mensagem .= 'Edição efetuada com sucesso.<br>';
                $this->simpleRedirect('educar_usuario_lst.php');
            }
        }

        $this->mensagem = 'Edição não realizada.<br>';

        return false;
    }

    public function Excluir()
    {
        $obj_funcionario = new clsPortalFuncionario($this->ref_pessoa);
        $detalhe = $obj_funcionario->detalhe();

        if ($obj_funcionario->excluir()) {
            $auditoria = new clsModulesAuditoriaGeral('funcionario', $this->pessoa_logada, $this->ref_pessoa);
            $auditoria->exclusao($detalhe);
            $this->mensagem .= 'Exclusão efetuada com sucesso.<br>';
            $this->simpleRedirect('educar_usuario_lst.php');
        }

        $this->mensagem = 'Exclusão não realizada.<br>';

        return false;
    }

    public function validatesUniquenessOfMatricula($pessoaId, $matricula)
    {
        $sql = "select 1 from portal.funcionario where lower(matricula) = lower('$matricula') and ref_cod_pessoa_fj != $pessoaId";
        $db = new clsBanco();

        if ($db->CampoUnico($sql) == '1') {
            $this->mensagem = "A matrícula '$matricula' já foi usada, por favor, informe outra.";

            return false;
        }

        return true;
    }

    public function validatesPassword($matricula, $password)
    {
        $msg = '';

        if ($password == $matricula) {
            $msg = 'Informe uma senha diferente da matricula.';
        } elseif (strlen($password) < 8) {
            $msg = 'Por favor informe uma senha segura, com pelo menos 8 caracteres.';
        }

        if ($msg) {
            $this->mensagem = $msg;

            return false;
        }

        return true;
    }

    public function excluiTodosVinculosEscola($codUsuario)
    {
        $usuarioEscola = new clsPmieducarEscolaUsuario();
        $usuarioEscola->excluirTodos($codUsuario);
    }

    public function insereUsuarioEscolas($codUsuario, $escolas)
    {
        $this->excluiTodosVinculosEscola($codUsuario);

        foreach ($escolas as $e) {
            $usuarioEscola = new clsPmieducarEscolaUsuario();
            $usuarioEscola->ref_cod_usuario = $codUsuario;
            $usuarioEscola->ref_cod_escola = $e;
            $usuarioEscola->cadastra();
        }
    }
}

$pagina = new clsIndexBase();
$miolo = new indice();

$pagina->addForm($miolo);
$pagina->MakeAll();
