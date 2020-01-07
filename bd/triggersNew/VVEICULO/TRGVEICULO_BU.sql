CREATE TRIGGER [dbo].[TRGVVEICULO_BU] ON [dbo].[VVEICULO]
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
  DECLARE @vclCodigoNew VARCHAR(10);
  DECLARE @vclNomeNew VARCHAR(40);
  DECLARE @vclFrotaNew VARCHAR(1);
  DECLARE @vclCodUniNew INTEGER;
  DECLARE @vclEntraBiNew VARCHAR(1);
  DECLARE @vclDtCalibracaoNew DATE;
  DECLARE @vclNumFrotaNew VARCHAR(20);    
  DECLARE @uniApelidoNew VARCHAR(15);  
  DECLARE @vclAtivoNew VARCHAR(1);
  DECLARE @vclCodGpoNew INTEGER;
  DECLARE @gpoNomeNew VARCHAR(60);
  DECLARE @vclRegNew VARCHAR(1);
  DECLARE @vclCodUsrNew INTEGER;
  DECLARE @usrApelidoNew VARCHAR(15);
  DECLARE @usrAdmPubNew VARCHAR(1);
  -------------------------------------------------------
  -- Buscando os campos NEW para checagem antes do insert
  -------------------------------------------------------
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
         ,@vclCodGpoNew       = i.VCL_CODGPO
         ,@gpoNomeNew         = COALESCE(GPO.GPO_NOME, 'ERRO')
         ,@usrApelidoNew      = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@usrAdmPubNew       = COALESCE(USR.USR_ADMPUB,'P')         
         ,@direitoNew         = UP.UP_D09
    FROM inserted i
    LEFT OUTER JOIN USUARIO USR ON i.VCL_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO
    LEFT OUTER JOIN GRUPOOPERACIONAL GPO ON i.VCL_CODGPO=GPO.GPO_CODIGO
    LEFT OUTER JOIN UNIDADE UNI ON i.VCL_CODUNI=UNI.UNI_CODIGO;
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@vclCodUsrNew);
  IF( @uniApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO UNIDADE %i PARA ESTE REGISTRO',15,1,@vclCodUniNew);
  IF( @gpoNomeNew='ERRO' )
  BEGIN
    SET @vclCodGpoNew=null;
  END 
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoNew<3 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 09 PARA INCLUIR NA TABELA VEICULO',15,1,@usrApelidoNew);
  --
  --
  ------------------------------------------------------------------------------------
  -- Se checar até aqui verifico os campos que estão no banco de dados antes de gravar  
  -- Campos OLD da tabela
  ------------------------------------------------------------------------------------
  DECLARE @vclCodigoOld VARCHAR(10);
  DECLARE @vclNomeOld VARCHAR(40);
  DECLARE @vclFrotaOld VARCHAR(1);
  DECLARE @vclCodUniOld INTEGER;
  DECLARE @vclEntraBiOld VARCHAR(1);
  DECLARE @vclDtCalibracaoOld DATE;  
  DECLARE @vclNumFrotaOld VARCHAR(20);
  DECLARE @vclCodGpoOld INTEGER;
  DECLARE @vclAtivoOld VARCHAR(1);
  DECLARE @vclRegOld VARCHAR(1);
  DECLARE @vclCodUsrOld INTEGER;
  SELECT @vclCodigoOld        = o.VCL_CODIGO
         ,@vclNomeOld         = o.VCL_NOME
         ,@vclFrotaOld        = o.VCL_FROTA
         ,@vclCodUniOld       = o.VCL_CODUNI
         ,@vclEntraBiOld      = o.VCL_ENTRABI
         ,@vclDtCalibracaoOld = o.VCL_DTCALIBRACAO
         ,@vclNumFrotaOld     = o.VCL_NUMFROTA
         ,@vclCodGpoOld       = o.VCL_CODGPO
         ,@vclAtivoOld        = o.VCL_ATIVO
         ,@vclRegOld          = o.VCL_REG
         ,@vclCodUsrOld       = o.VCL_CODUSR         
    FROM VEICULO o WHERE o.VCL_CODIGO=@vclCodigoNew;  
  ---------------------------------------------------------------------
  -- Primary Key nao pode ser CREATEada
  ---------------------------------------------------------------------
  IF( @vclCodigoOld<>@vclCodigoNew )
    RAISERROR('CAMPO CODIGO NAO PODE SER CREATEADO',15,1);  
  ---------------------------------------------------------------------
  -- Descritivo nao pode ser duplicado
  ---------------------------------------------------------------------
  --IF( @vclNomeOld<>@vclNomeNew ) BEGIN
  --  SELECT @fkStrNew=COALESCE(VCL_CODIGO,'') FROM VEICULO WHERE VCL_NOME=@vclNomeNew;
  --  IF( @fkStrNew<>'' )
  --    RAISERROR('DESCRITIVO JA CADASTRADO NA TABELA VEICULO %s',15,1,@fkStrNew);
  --END   
  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  IF( @vclRegOld<>@vclRegNew ) BEGIN
    SET @erroNew=dbo.fncCampoRegAlt( @usrAdmPubNew,@vclRegOld,@vclRegNew,4 );
    IF( @erroNew <> 'OK' )
      RAISERROR(@erroNew,15,1);
  END    
  --  
  BEGIN TRY
    UPDATE dbo.VEICULO
       SET VCL_NOME         = @vclNomeNew
          ,VCL_FROTA        = @vclFrotaNew
          ,VCL_CODUNI       = @vclCodUniNew
          ,VCL_ENTRABI      = @vclEntraBiNew
          ,VCL_DTCALIBRACAO = @vclDtCalibracaoNew
          ,VCL_NUMFROTA     = @vclNumFrotaNew
          ,VCL_ATIVO        = @vclAtivoNew
          ,VCL_CODGPO       = @vclCodGpoNew
          ,VCL_REG          = @vclRegNew
          ,VCL_CODUSR       = @vclCodUsrNew
    WHERE VCL_CODIGO  = @vclCodigoNew;     
    ---------------------------------------------------
    -- Atualizando a qtdade de veiculos em cada unidade
    ---------------------------------------------------
    IF( @vclAtivoOld<>@vclAtivoNew ) BEGIN
      IF( @vclAtivoNew='S' ) BEGIN
        UPDATE UNIDADE SET UNI_QTOSVCL=(UNI_QTOSVCL+1) WHERE UNI_CODIGO=@vclCodUniNew;
      END ELSE BEGIN  
        UPDATE UNIDADE SET UNI_QTOSVCL=(UNI_QTOSVCL-1) WHERE UNI_CODIGO=@vclCodUniNew;
      END  
    END;  
    ---------------
    -- Gravando LOG
    ---------------
    IF( (@vclNomeOld<>@vclNomeNew) OR (@vclFrotaOld<>@vclFrotaNew) OR (@vclCodUniOld<>@vclCodUniNew) 
     OR (@vclEntraBiOld<>@vclEntraBiNew) OR (@vclDtCalibracaoOld<>@vclDtCalibracaoNew) OR (@vclNumFrotaOld<>@vclNumFrotaNew) OR (@vclAtivoOld<>@vclAtivoNew) 
     OR (@vclRegOld<>@vclRegNew) ) BEGIN
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
        ,VCL_CODGPO
        ,VCL_REG
        ,VCL_CODUSR) VALUES(
        'A'                       -- VCL_ACAO
        ,@vclCodigoNew            -- VCL_CODIGO
        ,@vclNomeNew              -- VCL_NOME
        ,@vclFrotaNew             -- VCL_FROTA
        ,@vclCodUniNew            -- VCL_CODUNI
        ,@vclEntraBiNew           -- VCL_ENTRABI
        ,@vclDtCalibracaoNew      -- VCL_DTCALIBRACAO
        ,@vclNumFrotaNew          -- VCL_NUMFROTA        
        ,@vclAtivoNew             -- VCL_ATIVO
        ,@vclCodGpoNew             -- VCL_CODGPO
        ,@vclRegNew               -- VCL_REG
        ,@vclCodUsrNew            -- VCL_CODUSR
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