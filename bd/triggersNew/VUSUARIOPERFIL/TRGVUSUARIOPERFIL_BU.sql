CREATE TRIGGER [dbo].[TRGVUSUARIOPERFIL_BU] ON [dbo].[VUSUARIOPERFIL]
INSTEAD OF UPDATE
AS
BEGIN
  SET NOCOUNT ON;  
  DECLARE @direitoNew INTEGER;        -- Recupera o direito de usuario para esta tabela
  DECLARE @fkIntNew INTEGER = 0;      -- Para procurar campo foreign key int
  DECLARE @fkStrNew VARCHAR(3) = '';  -- Para procurar campo foreign key str
  DECLARE @erroNew VARCHAR(70);       -- Buscando retorno de erro para funcao
  -----------------------
  -- Campos NEW da tabela
  -----------------------
  DECLARE @upCodigoNew VARCHAR(3);
  DECLARE @upNomeNew VARCHAR(20);
  DECLARE @upD01New INTEGER;
  DECLARE @upD02New INTEGER;
  DECLARE @upD03New INTEGER;
  DECLARE @upD04New INTEGER;
  DECLARE @upD05New INTEGER;
  DECLARE @upD06New INTEGER;
  DECLARE @upD07New INTEGER;
  DECLARE @upD08New INTEGER;
  DECLARE @upD09New INTEGER;
  DECLARE @upD10New INTEGER;
  DECLARE @upD11New INTEGER;
  DECLARE @upD12New INTEGER;
  DECLARE @upD13New INTEGER;
  DECLARE @upD14New INTEGER;
  DECLARE @upD15New INTEGER;
  DECLARE @upD16New INTEGER;
  DECLARE @upD17New INTEGER;
  DECLARE @upD18New INTEGER;
  DECLARE @upD19New INTEGER;
  DECLARE @upD20New INTEGER;
  DECLARE @upAtivoNew VARCHAR(1);
  DECLARE @upRegNew VARCHAR(1);
  DECLARE @upCodUsrNew INTEGER;
  DECLARE @usrApelidoNew VARCHAR(15);
  DECLARE @usrAdmPubNew VARCHAR(1);
  DECLARE @consultarRelatorioNew VARCHAR(1);
  DECLARE @grupoOperacionalNew VARCHAR(1);
  -------------------------------------------------------
  -- Buscando os campos NEW para checagem antes do insert
  -------------------------------------------------------
  SELECT @upCodigoNew    = i.UP_CODIGO
         ,@upNomeNew     = UPPER(i.UP_NOME)
         ,@upD01New      = i.UP_D01
         ,@upD02New      = i.UP_D02
         ,@upD03New      = i.UP_D03
         ,@upD04New      = i.UP_D04
         ,@upD05New      = i.UP_D05
         ,@upD06New      = i.UP_D06
         ,@upD07New      = i.UP_D07
         ,@upD08New      = i.UP_D08
         ,@upD09New      = i.UP_D09
         ,@upD10New      = i.UP_D10
         ,@upD11New      = i.UP_D11
         ,@upD12New      = i.UP_D12
         ,@upD13New      = i.UP_D13
         ,@upD14New      = i.UP_D14
         ,@upD15New      = i.UP_D15
         ,@upD16New      = i.UP_D16
         ,@upD17New      = i.UP_D17
         ,@upD18New      = i.UP_D18
         ,@upD19New      = i.UP_D19
         ,@upD20New      = i.UP_D20
         ,@upAtivoNew    = UPPER(i.UP_ATIVO)
         ,@upRegNew      = UPPER(i.UP_REG)
         ,@upCodUsrNew   = i.UP_CODUSR         
         ,@usrApelidoNew = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@usrAdmPubNew  = COALESCE(USR.USR_ADMPUB,'P')
         ,@direitoNew    = UP.UP_D04
         ,@grupoOperacionalNew    = UPPER(i.GRUPO_OPERACIONAL)
         ,@consultarRelatorioNew    = UPPER(i.CONSULTAR_RELATORIO)
    FROM inserted i
    LEFT OUTER JOIN USUARIO USR ON i.UP_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO;    
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@upCodUsrNew);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoNew<3 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 03 PARA INCLUIR NA TABELA USUARIOPERFIL',15,1,@usrApelidoNew);
  --
  --
  ------------------------------------------------------------------------------------
  -- Se checar até aqui verifico os campos que estão no banco de dados antes de gravar  
  -- Campos OLD da tabela
  ------------------------------------------------------------------------------------
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
  DECLARE @grupoOperacionalOld VARCHAR(1);
  DECLARE @consultarRelatorioOld VARCHAR(1);
  SELECT @upCodigoOld   = o.UP_CODIGO
         ,@upNomeOld    = o.UP_NOME
         ,@upD01Old     = o.UP_D01
         ,@upD02Old     = o.UP_D02
         ,@upD03Old     = o.UP_D03
         ,@upD04Old     = o.UP_D04
         ,@upD05Old     = o.UP_D05
         ,@upD06Old     = o.UP_D06
         ,@upD07Old     = o.UP_D07
         ,@upD07Old     = o.UP_D07
         ,@upD08Old     = o.UP_D08
         ,@upD09Old     = o.UP_D09
         ,@upD10Old     = o.UP_D10
         ,@upD11Old     = o.UP_D11
         ,@upD12Old     = o.UP_D12
         ,@upD13Old     = o.UP_D13
         ,@upD14Old     = o.UP_D14
         ,@upD15Old     = o.UP_D15
         ,@upD16Old     = o.UP_D16
         ,@upD17Old     = o.UP_D17
         ,@upD18Old     = o.UP_D18
         ,@upD19Old     = o.UP_D19
         ,@upD20Old     = o.UP_D20
         ,@upAtivoOld   = o.UP_ATIVO
         ,@upRegOld     = o.UP_REG
         ,@upCodUsrOld  = o.UP_CODUSR
         ,@upAtivoOld   = o.UP_ATIVO
         ,@grupoOperacionalOld   = o.GRUPO_OPERACIONAL
         ,@consultarRelatorioOld   = o.CONSULTAR_RELATORIO
    FROM USUARIOPERFIL o WHERE o.UP_CODIGO=@upCodigoNew;
  ---------------------------------------------------------------------
  -- Primary Key nao pode ser CREATEada
  ---------------------------------------------------------------------
  IF( @upCodigoOld<>@upCodigoNew )
    RAISERROR('CAMPO CODIGO NAO PODE SER CREATEADO',15,1);  
  ---------------------------------------------------------------------
  -- Descritivo nao pode ser duplicado
  ---------------------------------------------------------------------
  IF( @upNomeOld<>@upNomeNew ) BEGIN
    SELECT @fkStrNew=COALESCE(UP_CODIGO,'') FROM USUARIOPERFIL WHERE UP_NOME=@upNomeNew;
    IF( @fkStrNew<>'' )
      RAISERROR('DESCRITIVO JA CADASTRADO NA TABELA USUARIOPERFIL %s',15,1,@fkStrNew);
  END   
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  IF( @upRegOld<>@upRegNew ) BEGIN
    SET @erroNew=dbo.fncCampoRegAlt( @usrAdmPubNew,@upRegOld,@upRegNew,4 );
    IF( @erroNew <> 'OK' )
      RAISERROR(@erroNew,15,1);
  END    
  --  
  BEGIN TRY
    UPDATE dbo.USUARIOPERFIL
       SET UP_NOME   = @upNomeNew
          ,UP_D01    = @upD01New
          ,UP_D02    = @upD02New
          ,UP_D03    = @upD03New
          ,UP_D04    = @upD04New
          ,UP_D05    = @upD05New
          ,UP_D06    = @upD06New
          ,UP_D07    = @upD07New
          ,UP_D08    = @upD08New
          ,UP_D09    = @upD09New
          ,UP_D10    = @upD10New
          ,UP_D11    = @upD11New
          ,UP_D12    = @upD12New
          ,UP_D13    = @upD13New
          ,UP_D14    = @upD14New
          ,UP_D15    = @upD15New
          ,UP_D16    = @upD16New
          ,UP_D17    = @upD17New
          ,UP_D18    = @upD18New
          ,UP_D19    = @upD19New
          ,UP_D20    = @upD20New
          ,UP_ATIVO  = @upAtivoNew
          ,UP_REG    = @upRegNew
          ,UP_CODUSR = @upCodUsrNew
          ,GRUPO_OPERACIONAL = @grupoOperacionalNew
          ,CONSULTAR_RELATORIO = @consultarRelatorioNew
    WHERE UP_CODIGO  = @upCodigoNew;
    ---------------
    -- Gravando LOG
    ---------------
    IF( (@upNomeOld<>@upNomeNew) OR (@upAtivoOld<>@upAtivoNew) OR (@upRegOld<>@upRegNew) OR (@consultarRelatorioOld<>@consultarRelatorioNew)
     OR (@upD01Old<>@upD01New) OR (@upD02Old<>@upD02New) OR (@upD03Old<>@upD03New) OR (@upD04Old<>@upD04New) OR (@upD05Old<>@upD05New) 
     OR (@upD06Old<>@upD06New) OR (@upD07Old<>@upD07New) OR (@upD08Old<>@upD08New) OR (@upD09Old<>@upD09New) OR (@upD10Old<>@upD10New) 
     OR (@upD11Old<>@upD01New) OR (@upD12Old<>@upD02New) OR (@upD13Old<>@upD03New) OR (@upD14Old<>@upD04New) OR (@upD15Old<>@upD05New) 
     OR (@upD16Old<>@upD06New) OR (@upD17Old<>@upD07New) OR (@upD18Old<>@upD08New) OR (@upD19Old<>@upD09New) OR (@upD20Old<>@upD10New) ) BEGIN
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
        'A'                      -- UP_ACAO
        ,@upCodigoNew            -- UP_CODIGO
        ,@upNomeNew              -- UP_NOME
        ,@upD01New,@upD02New,@upD03New,@upD04New,@upD05New,@upD06New,@upD07New,@upD08New,@upD09New,@upD10New
        ,@upD11New,@upD12New,@upD13New,@upD14New,@upD15New,@upD16New,@upD17New,@upD18New,@upD19New,@upD20New
        ,@upAtivoNew             -- UP_ATIVO
        ,@upRegNew               -- UP_REG
        ,@upCodUsrNew            -- UP_CODUSR
        ,@grupoOperacionalNew    -- GRUPO_OPERACIONAL
        ,@consultarRelatorioNew  -- CONSULTAR_RELATORIO
      );
    END
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