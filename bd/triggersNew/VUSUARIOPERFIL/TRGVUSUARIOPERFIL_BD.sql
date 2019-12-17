CREATE TRIGGER [dbo].[TRGVUSUARIOPERFIL_BD] ON [dbo].[VUSUARIOPERFIL]
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
  DECLARE @upCodigoOld VARCHAR(3);
  DECLARE @upNomeOld VARCHAR(20);
  DECLARE @upD01Old INTEGER;
  DECLARE @upD02Old INTEGER;
  DECLARE @upD03Old INTEGER;
  DECLARE @upD04Old INTEGER;
  DECLARE @upD05Old INTEGER;
  DECLARE @upD06Old INTEGER;
  DECLARE @upD07Old INTEGER;
  DECLARE @upD08Old INTEGER;
  DECLARE @upD09Old INTEGER;
  DECLARE @upD10Old INTEGER;
  DECLARE @upD11Old INTEGER;
  DECLARE @upD12Old INTEGER;
  DECLARE @upD13Old INTEGER;
  DECLARE @upD14Old INTEGER;
  DECLARE @upD15Old INTEGER;
  DECLARE @upD16Old INTEGER;
  DECLARE @upD17Old INTEGER;
  DECLARE @upD18Old INTEGER;
  DECLARE @upD19Old INTEGER;
  DECLARE @upD20Old INTEGER;
  DECLARE @upAtivoOld VARCHAR(1);
  DECLARE @upRegOld VARCHAR(1);
  DECLARE @upCodUsrOld INTEGER;
  DECLARE @usrApelidoOld VARCHAR(15);
  DECLARE @consultarRelatorioOld VARCHAR(1);
  DECLARE @grupoOperacionalOld VARCHAR(1);
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
  SELECT @upCodigoOld   = d.UP_CODIGO
         ,@upNomeOld    = d.UP_NOME
         ,@upD01Old     = d.UP_D01
         ,@upD02Old     = d.UP_D02
         ,@upD03Old     = d.UP_D03
         ,@upD04Old     = d.UP_D04
         ,@upD05Old     = d.UP_D05
         ,@upD06Old     = d.UP_D06
         ,@upD07Old     = d.UP_D07
         ,@upD08Old     = d.UP_D08
         ,@upD09Old     = d.UP_D09
         ,@upD10Old     = d.UP_D10
         ,@upD11Old     = d.UP_D11
         ,@upD12Old     = d.UP_D12
         ,@upD13Old     = d.UP_D13
         ,@upD14Old     = d.UP_D14
         ,@upD15Old     = d.UP_D15
         ,@upD16Old     = d.UP_D16
         ,@upD17Old     = d.UP_D17
         ,@upD18Old     = d.UP_D18
         ,@upD19Old     = d.UP_D19
         ,@upD20Old     = d.UP_D20
         ,@upAtivoOld   = d.UP_ATIVO
         ,@upRegOld     = d.UP_REG
         ,@upCodUsrOld  = d.UP_CODUSR         
         ,@usrApelidoOld = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@direitoOld    = UP.UP_D04
         ,@grupoOperacionalOld    = d.GRUPO_OPERACIONAL
         ,@consultarRelatorioOld = d.CONSULTAR_RELATORIO
    FROM deleted d
    LEFT OUTER JOIN USUARIO USR ON d.UP_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO;    
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoOld='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@upCodUsrOld);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoOld<4 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 03 PARA EXCLUIR NA TABELA USUARIOPERFIL',15,1,@usrApelidoOld);
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  SET @erroOld=dbo.fncCampoRegExc( @usrAdmPubOld,@upRegOld );
  IF( @erroOld != 'OK' )
    RAISERROR(@erroOld,15,1);
  -------------------------------
  -- Verificando o relacionamento
  -------------------------------
  SELECT TOP 1 @fkIntOld=COALESCE(USR_CODIGO,0) FROM USUARIO WHERE USR_CODUP=@upCodigoOld;
  IF( @fkIntOld>0 )
    RAISERROR('PERFIL UTILIZADO NO USUARIO %i',15,1,@fkIntOld);
  --
  --
  BEGIN TRY
    DELETE FROM dbo.USUARIOPERFIL WHERE UP_CODIGO=@upCodigoOld;
    ---------------
    -- Gravando LOG
    ---------------
    INSERT INTO dbo.BKPUSUARIOPERFIL(
      UP_ACAO
      ,UP_CODIGO
      ,UP_NOME
      ,UP_D01,UP_D02,UP_D03,UP_D04,UP_D05,UP_D06,UP_D07,UP_D08,UP_D09,UP_D10
      ,UP_D11,UP_D12,UP_D13,UP_D14,UP_D15,UP_D16,UP_D17,UP_D18,UP_D19,UP_D20
      ,UP_ATIVO
      ,UP_REG
      ,UP_CODUSR
      ,GRUPO_OPERACIONAL
      ,CONSULTAR_RELATORIO) VALUES(
      'E'                      -- UP_ACAO
      ,@upCodigoOld            -- UP_CODIGO
      ,@upNomeOld              -- UP_NOME
      ,@upD01Old,@upD02Old,@upD03Old,@upD04Old,@upD05Old,@upD06Old,@upD07Old,@upD08Old,@upD09Old,@upD10Old
      ,@upD11Old,@upD12Old,@upD13Old,@upD14Old,@upD15Old,@upD16Old,@upD17Old,@upD18Old,@upD19Old,@upD20Old
      ,@upAtivoOld             -- UP_ATIVO
      ,@upRegOld               -- UP_REG
      ,@upCodUsrOld            -- UP_CODUSR
      ,@grupoOperacionalOld    -- GRUPO_OPERACIONAL
      ,@consultarRelatorioOld  -- CONSULTAR_RELATORIO
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

