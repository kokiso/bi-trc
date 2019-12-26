CREATE TRIGGER [dbo].[TRGVVEICULO_BI] ON [dbo].[VVEICULO]
INSTEAD OF INSERT 
AS
BEGIN
   -- CAMPO          |INS|UPD|DEL| TIPO               Obs
   -- -----------------|---|---|---|--------------------|----------------------------------------------------------
   -- VCL_CODIGO       |   |   |   | VC(10) NN PK       | Codigo informado pelo usuario
   -- VCL_NOME         |   |   |   | VC(40) NN          | 
   -- VCL_FROTA        |   |   |   | VC(1) NN check     | L|P  Leve ou Pesado   
   -- VCL_CODUNI       |   |   |   | INT NN             | Codigo da UNIDADE em FK(UNIDADE)   
   -- VCL_ENTRABI      |   |   |   | VC(1) NN check     | S|N     Se o vai entrar nas tabelas de BI
   -- VCL_DTCALIBRACAO |   |   |   | DATE NN            | 
   -- VCL_NUMFROTA     |   |   |   | VC(20) NN          | Numero da frota ou NSA   
   -- VCL_ATIVO        |   |   |   | VC(1) NN check     | S|N     Se o registro pode ser usado em tabelas auxiliares
   -- VCL_REG          |   |   |   | VC(1) NN check     | P|A|S   P=Publico  A=Administrador S=Sistema 
   -- VCL_CODUSR       |   |   |   | INT NN             | Codigo do Usuario em USUARIO que esta tentando INC/ALT/EXC
   -- USR_APELIDO      |   |   |   | VC(15) NN          | Campo relacionado (USUARIO)
   -- -----------------|---|---|---|--------------------|----------------------------------------------------------   
   -- O Direito desta tabela em USUARIOPERFEIL(Ver select) 
  SET NOCOUNT ON;  
  DECLARE @direitoNew INTEGER;        -- Recupera o direito de usuario para esta tabela
  DECLARE @fkIntNew INTEGER = 0;      -- Para procurar campo foreign key int
  DECLARE @fkStrNew VARCHAR(20) = ''; -- Para procurar campo foreign key str
  DECLARE @erroNew VARCHAR(70);       -- Buscando retorno de erro para funcao
  -------------------
  -- Campos da tabela
  -------------------
  DECLARE @vclCodigoNew VARCHAR(10);
  DECLARE @vclNomeNew VARCHAR(40);
  DECLARE @vclFrotaNew VARCHAR(1);
  DECLARE @vclCodUniNew INTEGER;
  DECLARE @vclEntraBiNew VARCHAR(1);
  DECLARE @vclDtCalibracaoNew DATE;
  DECLARE @vclNumFrotaNew VARCHAR(20);  
  DECLARE @uniApelidoNew VARCHAR(15);
  DECLARE @vclAtivoNew VARCHAR(1);
  DECLARE @vclRegNew VARCHAR(1);
  DECLARE @vclCodUsrNew INTEGER;
  DECLARE @usrApelidoNew VARCHAR(15);
  DECLARE @usrAdmPubNew VARCHAR(1);
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
  SELECT @vclCodigoNew        = i.VCL_CODIGO
         ,@vclNomeNew         = UPPER(i.VCL_NOME)
         ,@vclFrotaNew        = UPPER(i.VCL_FROTA)
         ,@vclCodUniNew       = i.VCL_CODUNI
         ,@vclEntraBiNew      = UPPER(i.VCL_ENTRABI)
         ,@vclDtCalibracaoNew = i.VCL_DTCALIBRACAO
         ,@vclNumFrotaNew     = COALESCE(i.VCL_NUMFROTA,'NSA')
         ,@uniApelidoNew      = COALESCE(UNI.UNI_APELIDO,'ERRO')
         ,@vclAtivoNew        = UPPER(i.VCL_ATIVO)
         ,@vclRegNew          = UPPER(i.VCL_REG)
         ,@vclCodUsrNew       = i.VCL_CODUSR         
         ,@usrApelidoNew      = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@usrAdmPubNew       = COALESCE(USR.USR_ADMPUB,'P')         
         ,@direitoNew         = UP.UP_D09
    FROM inserted i
    LEFT OUTER JOIN USUARIO USR ON i.VCL_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO
    LEFT OUTER JOIN UNIDADE UNI ON i.VCL_CODUNI=UNI.UNI_CODIGO;
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@vclCodUsrNew);
  IF( @uniApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO UNIDADE %i PARA ESTE REGISTRO',15,1,@vclCodUniNew);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoNew<2 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 09 PARA INCLUIR NA TABELA VEICULO',15,1,@usrApelidoNew);
  ---------------------------------------------------------------------
  -- Razao social e apelido devem ser grpcos para grades sistema
  ---------------------------------------------------------------------
  --SELECT @fkIntNew=COALESCE(VCL_CODIGO,0) FROM VEICULO WHERE VCL_NOME=@vclNomeNew;
  --IF( @fkIntNew<>0 )
  --  RAISERROR('DESCRITIVO JA CADASTRADO NA TABELA VEICULO %i',15,1,@fkIntNew);
  ---------------------------------------------------------------------
  -- Verificando a chave primaria quando nao for identity
  ---------------------------------------------------------------------
  SELECT @fkStrNew=COALESCE(VCL_NOME,'') FROM VEICULO WHERE VCL_CODIGO=@vclCodigoNew;
  IF( COALESCE(@fkStrNew,'')<>'' )
    RAISERROR('CODIGO JA CADASTRADO NA TABELA VEICULO %s',15,1,@fkStrNew);
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  SET @erroNew=dbo.fncCampoRegInc( @usrAdmPubNew,@vclRegNew,4 );
  IF( @erroNew != 'OK' )
    RAISERROR(@erroNew,15,1);
  --  
  BEGIN TRY
    INSERT INTO dbo.VEICULO( 
      VCL_CODIGO
      ,VCL_NOME
      ,VCL_FROTA
      ,VCL_CODUNI
      ,VCL_ENTRABI
      ,VCL_DTCALIBRACAO
      ,VCL_NUMFROTA
      ,VCL_ATIVO
      ,VCL_REG
      ,VCL_CODUSR) VALUES(
      @vclCodigoNew        -- VCL_CODIGO
      ,@vclNomeNew         -- VCL_NOME
      ,@vclFrotaNew        -- VCL_FROTA
      ,@vclCodUniNew       -- VCL_CODUNI
      ,@vclEntraBiNew      -- VCL_ENTRABI
      ,@vclDtCalibracaoNew -- VCL_DTCALIBRACAO      
      ,@vclNumFrotaNew     -- VCL_NUMFROTA
      ,@vclAtivoNew        -- VCL_ATIVO
      ,@vclRegNew          -- VCL_REG
      ,@vclCodUsrNew       -- VCL_CODUSR
    );
    ---------------------------------------------------
    -- Atualizando a qtdade de veiculos em cada unidade
    ---------------------------------------------------
    IF( @vclAtivoNew='S' )
      UPDATE UNIDADE SET UNI_QTOSVCL=(UNI_QTOSVCL+1) WHERE UNI_CODIGO=@vclCodUniNew;
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
      'I'                       -- VCL_ACAO
      ,@vclCodigoNew            -- VCL_CODIGO
      ,@vclNomeNew              -- VCL_NOME
      ,@vclFrotaNew             -- VCL_FROTA
      ,@vclCodUniNew            -- VCL_CODUNI
      ,@vclEntraBiNew           -- VCL_ENTRABI
      ,@vclDtCalibracaoNew      -- VCL_DTCALIBRACAO
      ,@vclNumFrotaNew          -- VCL_NUMFROTA
      ,@vclAtivoNew             -- VCL_ATIVO
      ,@vclRegNew               -- VCL_REG
      ,@vclCodUsrNew            -- VCL_CODUSR
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