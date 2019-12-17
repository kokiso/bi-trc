CREATE TRIGGER [dbo].[TRGVUSUARIOPERFIL_BI] ON [dbo].[VUSUARIOPERFIL]
INSTEAD OF INSERT 
AS
BEGIN
   -- CAMPO          |INS|UPD|DEL| TIPO               Obs
   -- ---------------|---|---|---|--------------------|----------------------------------------------------------
   -- UP_CODIGO      |   |   |   | INT NN IDENTITY    | Codigo informado pelo usuario
   -- UP_NOME        |   |   |   | VC(20) NN          |
   -- UP_D01 A D20   |   |   |   | INT NN check       |      
   -- UP_ATIVO       |   |   |   | VC(1) NN check     | S|N     Se o registro pode ser usado em tabelas auxiliares
   -- UP_REG         |   |   |   | VC(1) NN check     | P|A|S   P=Publico  A=Administrador S=Sistema 
   -- UP_CODUSR      |   |   |   | INT NN             | Codigo do Usuario em USUARIO que esta tentando INC/ALT/EXC
   -- USR_APELIDO    |   |   |   | VC(15) NN          | Campo relacionado (USUARIO)
   -- ---------------|---|---|---|--------------------|----------------------------------------------------------   
   -- O Direito desta tabela em USUARIOPERFEIL(Ver select) 
  SET NOCOUNT ON;  
  DECLARE @direitoNew INTEGER;        -- Recupera o direito de usuario para esta tabela
  DECLARE @fkIntNew INTEGER = 0;      -- Para procurar campo foreign key int
  DECLARE @fkStrNew VARCHAR(20) = ''; -- Para procurar campo foreign key str
  DECLARE @erroNew VARCHAR(70);       -- Buscando retorno de erro para funcao
  -------------------
  -- Campos da tabela
  -------------------
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
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
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
         ,@consultarRelatorioNew    = UPPER(i.CONSULTAR_RELATORIO)
         ,@grupoOperacionalNew      = UPPER(i.GRUPO_OPERACIONAL)
    FROM inserted i
    LEFT OUTER JOIN USUARIO USR ON i.UP_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO;    
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO(USUARIOPERFIL)',15,1,@upCodUsrNew);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoNew<2 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 03 PARA INCLUIR NA TABELA USUARIOPERFIL',15,1,@usrApelidoNew);
  ---------------------------------------------------------------------
  -- Razao social e apelido devem ser grpcos para grades sistema
  ---------------------------------------------------------------------
  SELECT @fkIntNew=COALESCE(UP_CODIGO,0) FROM USUARIOPERFIL WHERE UP_NOME=@upNomeNew;
  IF( @fkIntNew<>0 )
    RAISERROR('DESCRITIVO JA CADASTRADO NA TABELA USUARIOPERFIL %i',15,1,@fkIntNew);
  ---------------------------------------------------------------------
  -- Verificando a chave primaria quando nao for identity
  ---------------------------------------------------------------------
  SELECT @fkStrNew=COALESCE(UP_NOME,0) FROM USUARIOPERFIL WHERE UP_CODIGO=@upCodigoNew;
  IF( COALESCE(@fkStrNew,'')<>'' )
    RAISERROR('CODIGO JA CADASTRADO NA TABELA USUARIOPERFIL %s',15,1,@fkStrNew);
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  SET @erroNew=dbo.fncCampoRegInc( @usrAdmPubNew,@upRegNew,4 );
  IF( @erroNew != 'OK' )
    RAISERROR(@erroNew,15,1);
  --  
  BEGIN TRY
    INSERT INTO dbo.USUARIOPERFIL( 
      UP_NOME
      ,UP_D01,UP_D02,UP_D03,UP_D04,UP_D05,UP_D06,UP_D07,UP_D08,UP_D09,UP_D10
      ,UP_D11,UP_D12,UP_D13,UP_D14,UP_D15,UP_D16,UP_D17,UP_D18,UP_D19,UP_D20
      ,UP_ATIVO
      ,UP_REG
      ,UP_CODUSR
      ,GRUPO_OPERACIONAL
      ,CONSULTAR_RELATORIO) VALUES(
      @upNomeNew    -- UP_NOME
      ,@upD01New,@upD02New,@upD03New,@upD04New,@upD05New,@upD06New,@upD07New,@upD08New,@upD09New,@upD10New
      ,@upD11New,@upD12New,@upD13New,@upD14New,@upD15New,@upD16New,@upD17New,@upD18New,@upD19New,@upD20New
      ,@upAtivoNew   -- UP_ATIVO
      ,@upRegNew     -- UP_REG
      ,@upCodUsrNew  -- UP_CODUSR
      ,@grupoOperacionalNew  -- GRUPO_OPERACIONAL
      ,@consultarRelatorioNew  -- CONSULTAR_RELATORIO
    );
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
      'I'                              -- UP_ACAO
      ,IDENT_CURRENT('USUARIOPERFIL')  -- UP_CODIGO
      ,@upNomeNew                      -- UP_NOME
      ,@upD01New,@upD02New,@upD03New,@upD04New,@upD05New,@upD06New,@upD07New,@upD08New,@upD09New,@upD10New
      ,@upD11New,@upD12New,@upD13New,@upD14New,@upD15New,@upD16New,@upD17New,@upD18New,@upD19New,@upD20New
      ,@upAtivoNew                     -- UP_ATIVO
      ,@upRegNew                       -- UP_REG
      ,@upCodUsrNew                    -- UP_CODUSR
      ,@grupoOperacionalNew            -- GRUPO_OPERACIONAL
      ,@consultarRelatorioNew          -- CONSULTAR_RELATORIO
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

