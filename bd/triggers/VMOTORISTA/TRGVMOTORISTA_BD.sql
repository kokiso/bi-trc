CREATE TRIGGER [dbo].[TRGVMOTORISTA_BD] ON [dbo].[VMOTORISTA]
INSTEAD OF DELETE
AS
BEGIN
  SET NOCOUNT ON;
  DECLARE @direitoOld INTEGER;        -- Recupera o direito de usuario para esta tabela
  DECLARE @usrAdmPubOld VARCHAR(1);   -- Retornar se o usuario eh PUB/ADM
  DECLARE @fkIntOld INTEGER = 0;      -- Para procurar campo foreign key int
  DECLARE @fkStrOld VARCHAR(20) = ''; -- Para procurar campo foreign key str
  DECLARE @erroOld VARCHAR(70);       -- Buscando retorno de erro para funcao
  -------------------
  -- Campos da tabela
  -------------------
  DECLARE @mtrCodigoOld INTEGER;
  DECLARE @mtrNomeOld VARCHAR(60);
  DECLARE @mtrRfidOld VARCHAR(30);
  DECLARE @mtrCodUniOld INTEGER;
  DECLARE @mtrAtivoOld VARCHAR(1);
  DECLARE @mtrRegOld VARCHAR(1);
  DECLARE @mtrCodUsrOld INTEGER;
  DECLARE @usrApelidoOld VARCHAR(15);
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
  SELECT @mtrCodigoOld   = d.MTR_CODIGO
         ,@mtrNomeOld    = d.MTR_NOME
         ,@mtrRfidOld    = d.MTR_RFID
         ,@mtrCodUniOld  = d.MTR_CODUNI
         ,@mtrAtivoOld   = d.MTR_ATIVO
         ,@mtrRegOld     = d.MTR_REG
         ,@mtrCodUsrOld  = d.MTR_CODUSR
         ,@usrApelidoOld = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@direitoOld    = UP.UP_D04
    FROM deleted d
    LEFT OUTER JOIN USUARIO USR ON d.MTR_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO;
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoOld='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@mtrCodUsrOld);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoOld<4 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 04 PARA EXCLUIR NA TABELA MOTORISTA',15,1,@usrApelidoOld);
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  SET @erroOld=dbo.fncCampoRegExc( @usrAdmPubOld,@mtrRegOld );
  IF( @erroOld != 'OK' )
    RAISERROR(@erroOld,15,1);
  -------------------------------
  -- Verificando o relacionamento
  -------------------------------
  SET @fkIntOld=0;
  SELECT TOP 1 @fkIntOld=COALESCE(BIAB_POSICAO,0) FROM BI_ACELERBRUSCA WHERE BIAB_CODMTR=@mtrCodigoOld;
  IF( @@rowcount>0 )
    RAISERROR('MOTORISTA UTILIZADO EM BI_ACELERACAO POSICAO %i',15,1,@fkIntOld);

  SET @fkIntOld=0;
  SELECT TOP 1 @fkIntOld=COALESCE(BIBV_POSICAO,0) FROM BI_BATERIAVIOLA WHERE BIBV_CODMTR=@mtrCodigoOld;
  IF( @@rowcount>0 )
    RAISERROR('MOTORISTA UTILIZADO EM BI_VIOLACAOBATERIA POSICAO %i',15,1,@fkIntOld);

  SET @fkIntOld=0;
  SELECT TOP 1 @fkIntOld=COALESCE(BICB_POSICAO,0) FROM BI_CONDUCAOBANG WHERE BICB_CODMTR=@mtrCodigoOld;
  IF( @@rowcount>0 )
    RAISERROR('MOTORISTA UTILIZADO EM BI_BANGUELA POSICAO %i',15,1,@fkIntOld);

  SET @fkIntOld=0;
  SELECT TOP 1 @fkIntOld=COALESCE(BIEVC_POSICAO,0) FROM BI_EXCESSOVELCH WHERE BIEVC_CODMTR=@mtrCodigoOld;
  IF( @@rowcount>0 )
    RAISERROR('MOTORISTA UTILIZADO EM BI_VELOCIDADECHUVA POSICAO %i',15,1,@fkIntOld);

  SET @fkIntOld=0;
  SELECT TOP 1 @fkIntOld=COALESCE(BIEV_POSICAO,0) FROM BI_EXCESSOVELOC WHERE BIEV_CODMTR=@mtrCodigoOld;
  IF( @@rowcount>0 )
    RAISERROR('MOTORISTA UTILIZADO EM BI_VELOCIDADE POSICAO %i',15,1,@fkIntOld);

  BEGIN TRY

--     DELETE FROM dbo.MOTORISTA WHERE MTR_CODIGO=@mtrCodigoOld;
--     REMOVENDO ANTIGO DELETE E APLICANDO EXCLUSÃO LÓGICA

    UPDATE dbo.MOTORISTA
       SET MTR_ATIVO  = 'N',
           MTR_EXCLUIDO = 'S'
    WHERE MTR_CODIGO = @mtrCodigoOld;

    UPDATE UNIDADE SET UNI_QTOSMTR=(UNI_QTOSMTR-1) WHERE UNI_CODIGO=@mtrCodUniOld;
    ---------------
    -- Gravando LOG
    ---------------
    INSERT INTO dbo.BKPMOTORISTA(
      MTR_ACAO
      ,MTR_CODIGO
      ,MTR_NOME
      ,MTR_RFID
      ,MTR_CODUNI
      ,MTR_ATIVO
      ,MTR_REG
      ,MTR_CODUSR) VALUES(
      'E'                       -- MTR_ACAO
      ,@mtrCodigoOld            -- MTR_CODIGO
      ,@mtrNomeOld              -- MTR_NOME
      ,@mtrRfidOld              -- MTR_RFID
      ,@mtrCodUniOld            -- MTR_CODUNI
      ,@mtrAtivoOld             -- MTR_ATIVO
      ,@mtrRegOld               -- MTR_REG
      ,@mtrCodUsrOld            -- MTR_CODUSR
    );
  END TRY
  BEGIN CATCH
    DECLARE @ErrorMessage NVARCHAR(4000);
    DECLARE @ErrorSeverity INT;
    DECLARE @ErrorState INT;
    SELECT @ErrorMessage=ERROR_MESSAGE(),@ErrorSeverity=ERROR_SEVERITY(),@ErrorState=ERROR_STATE();
    RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    RETURN;
  END CATCH
END
go

