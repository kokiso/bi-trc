CREATE TRIGGER [dbo].[TRGVVEICULO_BD] ON [dbo].[VVEICULO]
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
  DECLARE @vclCodigoOld VARCHAR(10);
  DECLARE @vclNomeOld VARCHAR(40);
  DECLARE @vclFrotaOld VARCHAR(1);
  DECLARE @vclCodUniOld INTEGER;
  DECLARE @vclEntraBiOld VARCHAR(1);
  DECLARE @vclDtCalibracaoOld DATE;  
  DECLARE @vclNumFrotaOld VARCHAR(20);  
  DECLARE @vclAtivoOld VARCHAR(1);
  DECLARE @vclRegOld VARCHAR(1);
  DECLARE @vclCodUsrOld INTEGER;
  DECLARE @usrApelidoOld VARCHAR(15);
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
  SELECT @vclCodigoOld        = d.VCL_CODIGO
         ,@vclNomeOld         = d.VCL_NOME
         ,@vclFrotaOld        = d.VCL_FROTA
         ,@vclCodUniOld       = d.VCL_CODUNI
         ,@vclEntraBiOld      = d.VCL_ENTRABI
         ,@vclDtCalibracaoOld = d.VCL_DTCALIBRACAO         
         ,@vclNumFrotaOld     = d.VCL_NUMFROTA         
         ,@vclAtivoOld        = d.VCL_ATIVO
         ,@vclRegOld          = d.VCL_REG
         ,@vclCodUsrOld       = d.VCL_CODUSR         
         ,@usrApelidoOld      = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@direitoOld         = UP.UP_D09
    FROM deleted d
    LEFT OUTER JOIN USUARIO USR ON d.VCL_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO;    
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoOld='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@vclCodUsrOld);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoOld<4 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 09 PARA EXCLUIR NA TABELA VEICULO',15,1,@usrApelidoOld);
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  SET @erroOld=dbo.fncCampoRegExc( @usrAdmPubOld,@vclRegOld );
  IF( @erroOld != 'OK' )
    RAISERROR(@erroOld,15,1);
  -------------------------------
  -- Verificando o relacionamento
  -------------------------------
  --SELECT TOP 1 @fkIntOld=COALESCE(USR_CODIGO,0) FROM USUARIO WHERE USR_CODCRG=@vclCodigoOld;
  --IF( @fkIntOld>0 )
  --  RAISERROR('VEICULO UTILIZADO NO USUARIO %i',15,1,@fkIntOld);
  --
  --
  BEGIN TRY
    DELETE FROM dbo.VEICULO WHERE VCL_CODIGO=@vclCodigoOld AND VCL_CODUNI=@vclCodUniOld;
    ---------------------------------------------------
    -- Atualizando a qtdade de veiculos em cada unidade
    ---------------------------------------------------
    UPDATE UNIDADE SET UNI_QTOSVCL=(UNI_QTOSVCL-1) WHERE UNI_CODIGO=@vclCodUniOld;
    ---------------
    -- Gravando LOG
    ---------------
    INSERT INTO dbo.BKPVEICULO(
      VCL_ACAO
      ,VCL_CODIGO
      ,VCL_NOME
      ,VCL_FROTA
      ,VCL_CODUNI
      ,VCL_ENTRABI
      ,VCL_DTCALIBRACAO
      ,VCL_NUMFROTA      
      ,VCL_ATIVO
      ,VCL_REG
      ,VCL_CODUSR) VALUES(
      'E'                   -- VCL_ACAO
      ,@vclCodigoOld        -- VCL_CODIGO
      ,@vclNomeOld          -- VCL_NOME
      ,@vclFrotaOld         -- VCL_FROTA
      ,@vclCodUniOld        -- VCL_CODUNI
      ,@vclEntraBiOld       -- VCL_ENTRABI
      ,@vclDtCalibracaoOld  -- VCL_DTCALIBRACAO      
      ,@vclNumFrotaOld      -- VCL_NUMFROTA      
      ,@vclAtivoOld         -- VCL_ATIVO
      ,@vclRegOld           -- VCL_REG
      ,@vclCodUsrOld        -- VCL_CODUSR
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

