CREATE TRIGGER [dbo].[TRGViewTMPMOVIMENTO_BI] ON [dbo].[VTMPMOVIMENTO]
INSTEAD OF INSERT 
AS
BEGIN
   -- CAMPO            |INS  |UPD |DEL | TIPO               | Obs
   -- -----------------|-----|----|----|--------------------|----------------------------------------------------------
   -- TMVM_POSICAO     |     |    |    | INT PK NN          |
   -- TMVM_CODVEI      |     |    |    | INT NN             |
   -- TMVM_PLACA       |     |    |    | VC(10) NN          |
   -- TMVM_CODUNI      |     |    |    | INT NN             |
   -- TMVM_RFID        |     |    |    | VC(30) NN          |
   -- TMVM_DESMTR      |     |    |    | VC(60) NN          |   
   -- TMVM_CODEVESS    |     |    |    | VC(13) NN          | Evento da SistemSat
   -- TMVM_DESEVE      |     |    |    | VC(80) NN          |
   -- TMVM_NUMEROSERIE |     |    |    | VC(40) NN          |
   -- TMVM_LATITUDE    |     |    |    | NUM(15,8) NN       |
   -- TMVM_LONGITUDE   |     |    |    | NUM(15,8) NN       |
   -- TMVM_VELOCIDADE  |     |    |    | INT NN             |
   -- TMVM_ODOMETRO    |     |    |    | NUM(15,4) NN       |
   -- TMVM_IGNICAO     |     |    |    | INT NN             |
   -- TMVM_TEMPERATURA |     |    |    | INT NN             |
   -- TMVM_DATAGPS     |     |    |    | DATE NN            |
   -- TMVM_HORAGPS     |     |    |    | INTEGER NN         |
   -- TMVM_HORIMETRO   |     |    |    | INT NN             |
   -- TMVM_RPM         |     |    |    | INT NN             |
   -- TMVM_LOCALIZACAO |     |    |    | VC(100) NN         |
   -- TMVM_ANOMES      |     |    |    | INT NN             |   
   -- -----------------|-----|----|----|--------------------|----------------------------------------------------------   
   -- [OK]=Checado no trigger   [CC]=Check constraint  [SEL]=Select  [FNC]=function
   -- -----------------|-----|----|----|--------------------|----------------------------------------------------------   
  SET NOCOUNT ON;  
  DECLARE @varPosicao BIGINT          = 0;        -- Para ver se nao vem ID duplicado
  DECLARE @varCodMtr INTEGER          = 0;        -- Para ver se cadastra o morotista
  DECLARE @varCodVcl VARCHAR(10)      = 'OK';     -- Para ver se cadastra o veiculo
  DECLARE @varCodEve INTEGER          = 0;        -- Para ver se cadastra o evento
  DECLARE @varCodEg VARCHAR(4)        = '****';   -- Para poder montar BIs por evento/grupo evento
  DECLARE @varCodEgErr VARCHAR(4)     = '****';   -- Para gravar BI_ERRO qdo converter
  DECLARE @tblErpm INTEGER            = 0;        -- Acumular na tabela mensal de Excesso RPM (BI_RPMALTOMES)
  DECLARE @tblAbm INTEGER             = 0;        -- Acumular na tabela mensal de Aceleracao bruscao (BI_ACELERBRUSCAMES)
  DECLARE @tblBvm INTEGER             = 0;        -- Acumular na tabela mensal de Aceleracao bruscao (BI_BATERIAVIOLA)
  DECLARE @tblCbm INTEGER             = 0;        -- Acumular na tabela mensal de Conducao banguela (BI_CONDUCAOBANGMES)
  DECLARE @tblEvm INTEGER             = 0;        -- Acumular na tabela mensal de Excesso velocidade (BI_EXCESSOVELOCMES)
  DECLARE @tblEvcm INTEGER            = 0;        -- Acumular na tabela mensal de Excesso velocidade chuva (BI_EXCESSOVELCHMES)
  DECLARE @tblFbm INTEGER             = 0;        -- Acumular na tabela mensal de Freada brusca (BI_FREADABRUSCAMES)
  DECLARE @tblPrdVm INTEGER           = 0;        -- Acumular na tabela mensal de Produtividade (BI_PRODUTIVIDADEVEIMES)
  DECLARE @tblPrdMm INTEGER           = 0;        -- Acumular na tabela mensal de Produtividade (BI_PRODUTIVIDADEMOTMES)
  DECLARE @tblVnm INTEGER             = 0;        -- Acumular na tabela mensal de Velocidade normalizada (BI_VELOCNORMALIMES)
  DECLARE @tblKmm INTEGER             = 0;        -- Acumular na tabela mensal de KILOMETRAGEM (BI_KILOMETROMES)
  DECLARE @odometroFim NUMERIC(15,4)  = 0;        -- Checando se o odometro do select eh maior do que ja esta na tabela
  -------------------
  -- Campos da tabela
  -------------------
  DECLARE @mvmPosicao BIGINT;
  DECLARE @mvmCodVei INTEGER;
  DECLARE @mvmPlaca VARCHAR(10);
  DECLARE @vclEntraBi VARCHAR(1);
  DECLARE @vclFrota VARCHAR(1);
  DECLARE @mvmCodUni INTEGER;
  DECLARE @uniApelido VARCHAR(15);
  DECLARE @mvmCodPol VARCHAR(3);
  DECLARE @mvmRfid VARCHAR(30);
  DECLARE @mvmCodMtr INTEGER;
  DECLARE @mvmDesMtr VARCHAR(30);
  DECLARE @mvmCodEveSS VARCHAR(13);   --Codigo do evento vindo da SistemSat
  DECLARE @mvmDesEve VARCHAR(80);
  DECLARE @mvmNumeroSerie VARCHAR(40);
  DECLARE @mvmLatitude NUMERIC(15,8);
  DECLARE @mvmLongitude NUMERIC(15,8);
  DECLARE @mvmVelocidade INTEGER;
  DECLARE @mvmOdometro NUMERIC(15,4);
  DECLARE @mvmIgnicao INTEGER;
  DECLARE @mvmTemperatura INTEGER;
  DECLARE @mvmDataGps DATETIME
  DECLARE @mvmHoraGps INTEGER;
  DECLARE @mvmHorimeto INTEGER;
  DECLARE @mvmRpm INTEGER;
  DECLARE @mvmLocalizacao VARCHAR(100);
  DECLARE @mvmAnoMes INTEGER;
  DECLARE @mvmTurno VARCHAR(1);    
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
  SELECT @mvmPosicao       =  i.TMVM_POSICAO    
         ,@mvmCodVei       =  i.TMVM_CODVEI     
         ,@mvmPlaca        =  i.TMVM_PLACA      
         ,@vclEntraBi      =  COALESCE(VCL.VCL_ENTRABI,'*')
         ,@mvmCodUni       =  i.TMVM_CODUNI
         ,@mvmCodPol       =  UNI.UNI_CODPOL
         ,@uniApelido      =  COALESCE(UNI.UNI_APELIDO,'ERRO')
         ,@mvmRfid         =  i.TMVM_RFID       
         ,@mvmDesMtr       =  i.TMVM_DESMTR     
         ,@mvmCodEveSS     =  i.TMVM_CODEVESS     
         ,@mvmDesEve       =  i.TMVM_DESEVE     
         ,@mvmNumeroSerie  =  i.TMVM_NUMEROSERIE
         ,@mvmLatitude     =  i.TMVM_LATITUDE   
         ,@mvmLongitude    =  i.TMVM_LONGITUDE  
         ,@mvmVelocidade   =  i.TMVM_VELOCIDADE 
         ,@mvmOdometro     =  i.TMVM_ODOMETRO   
         ,@mvmIgnicao      =  i.TMVM_IGNICAO    
         ,@mvmTemperatura  =  i.TMVM_TEMPERATURA
         ,@mvmDataGps      =  i.TMVM_DATAGPS    
         ,@mvmHoraGps      =  i.TMVM_HORAGPS    
         ,@mvmHorimeto     =  i.TMVM_HORIMETRO  
         ,@mvmRpm          =  i.TMVM_RPM        
         ,@mvmLocalizacao  =  i.TMVM_LOCALIZACAO
         ,@mvmAnoMes       =  i.TMVM_ANOMES
  FROM inserted i
  LEFT OUTER JOIN VEICULO VCL ON i.TMVM_PLACA=VCL.VCL_CODIGO
  LEFT OUTER JOIN UNIDADE UNI ON i.TMVM_CODUNI=UNI.UNI_CODIGO;
  -----------------------------------------------------
  -- Somente as unidades cadastradas vao ser integradas
  -- Somente os veiculos selecionados são cadastrados
  -----------------------------------------------------
  IF( ((@vclEntraBi='S') OR (@vclEntraBi='*')) AND (@uniApelido<>'ERRO') ) BEGIN
    -----------------------------------------------------
    -- No arquivo sistemsat o id pode vir duplicado
    -----------------------------------------------------
    SELECT @varPosicao=COALESCE(MVM_POSICAO,0) FROM MOVIMENTO WHERE MVM_POSICAO=@mvmPosicao;
	  IF( @@rowcount=0 ) BEGIN
      ---------------------------------------------------------
      -- Inserindo o motorista
      -- Pode existir RFID duplicado desde que nao esteja ativo
      -- Tem que ser na view para atualizar UNI_QTOSMTR
      ---------------------------------------------------------
      SELECT @varCodMtr=COALESCE(MTR_CODIGO,0) FROM MOTORISTA WHERE ((MTR_RFID=@mvmRfid) AND (MTR_NOME=@mvmDesMtr) AND (MTR_ATIVO='S'));
      IF( @varCodMtr=0 ) BEGIN
        SELECT @varCodMtr=(MAX(MTR_CODIGO)+1) FROM MOTORISTA;
        INSERT INTO dbo.VMOTORISTA(
          MTR_CODIGO  ,MTR_NOME    ,MTR_RFID ,MTR_CODUNI,MTR_ATIVO ,MTR_REG,MTR_POSICAO,MTR_CODUSR) VALUES(
          @varCodMtr  ,@mvmDesMtr  ,@mvmRfid ,@mvmCodUni,'S'       ,'P'    ,@mvmPosicao,1
        );
      END ELSE BEGIN
        UPDATE MOTORISTA SET MTR_POSICAO=@mvmPosicao WHERE MTR_CODIGO=@varCodMtr;
      END
      --
      --
      -------------------------------------------------
      -- Inserindo o veiculo
      -- Tem que ser na view para atualizar UNI_QTOSVCL
      -------------------------------------------------
      SELECT @varCodVcl=COALESCE(VCL_CODIGO,'OK'),@vclFrota=VCL_FROTA FROM VEICULO WHERE VCL_CODIGO=@mvmPlaca;
      IF( @varCodVcl='OK' ) BEGIN
        SET @vclFrota='P';
        INSERT INTO dbo.VVEICULO( 
          VCL_CODIGO  ,VCL_NOME               ,VCL_FROTA,VCL_CODUNI ,VCL_ENTRABI,VCL_DTCALIBRACAO,VCL_ATIVO,VCL_REG,VCL_CODUSR) VALUES(
          @mvmPlaca   ,'CADASTRO AUTOMATICO'  ,'P'      ,@mvmCodUni ,'S'        ,'1900-01-01'    ,'S'      ,'P'    ,1
        );
      END
      --
      --
      -------------------------------------------------
      -- Inserindo o turno
      -------------------------------------------------
      SET @mvmTurno='*';
      SELECT @mvmTurno=COALESCE(TRN_NOME,'*') FROM TURNO 
       WHERE (@mvmHoraGps BETWEEN TRN_INTI AND TRN_INTF)
         AND (TRN_FROTA=@vclFrota);   
      --
      --
      ------------------------
      -- Cadastrando o evento
      ------------------------
      SELECT @varCodEve=COALESCE(EVE_CODIGO,0),@varCodEg=EVE_CODEG FROM EVENTO WHERE EVE_NOME=@mvmDesEve;
      IF( @varCodEve=0 ) BEGIN
        SELECT @varCodEve=(MAX(EVE_CODIGO)+1) FROM EVENTO;
        INSERT INTO dbo.EVENTO( 
          EVE_CODIGO  ,EVE_NOME   ,EVE_CODEG  ,EVE_ATIVO,EVE_REG,EVE_CODUSR) VALUES(
          @varCodEve  ,@mvmDesEve ,'*'        ,'S'       ,'P'    ,1
        );
      END
      ---------------------
      -- Regra de 08jun2018
      ---------------------
      IF( (@varCodEg='EV') OR (@varCodEg='EVC') ) BEGIN
        SET @varCodEgErr=@varCodEg;
        -- Frota PESADA
        IF( @vclFrota='P' ) BEGIN
          IF( (@varCodEg='EV') AND (@mvmVelocidade<81) ) BEGIN
            SET @varCodEve = 201;
            SET @varCodEg  = '*';
          END
          IF( (@varCodEg='EVC') AND (@mvmVelocidade<61) ) BEGIN
            SET @varCodEve = 201;
            SET @varCodEg  = '*';
          END
          IF( @mvmVelocidade>120 ) BEGIN
            SET @varCodEve = 201;
            SET @varCodEg  = '*';
          END
        END
        -- Frota LEVE
        IF( @vclFrota='L' ) BEGIN
          IF( (@varCodEg='EV') AND (@mvmVelocidade<111) ) BEGIN
            SET @varCodEve = 201;
            SET @varCodEg  = '*';
          END
          IF( (@varCodEg='EVC') AND (@mvmVelocidade<91) ) BEGIN
            SET @varCodEve = 201;
            SET @varCodEg  = '*';
          END
          IF( @mvmVelocidade>150 ) BEGIN
            SET @varCodEve = 201;
            SET @varCodEg  = '*';
          END
        END
      END
      ------------------------------------------------------------------------------------------------------------------
      -- Gravando na tabela principal
      -- Acertado com Pedro em 05jul2018 que o campo Ignicao nao eh confiavel pois pode ser atualizado apenas no proximo
      -- evento, entao ajusto aqui pelo evento recebido da SS (IgnicaoLigado ou IgnicaoDesligada)
      -- Esse ajuste eh devido ao BI Produtividade
      ------------------------------------------------------------------------------------------------------------------
      IF( @varCodEve=44 ) 
        SET @mvmIgnicao=1;  
      IF( @varCodEve=52 ) 
        SET @mvmIgnicao=0;  
      --
      --
      INSERT INTO MOVIMENTO(
        MVM_POSICAO
        ,MVM_CODVEI
        ,MVM_PLACA
        ,MVM_CODUNI
        ,MVM_CODPOL
        ,MVM_RFID
        ,MVM_CODMTR
        ,MVM_CODEVESS
        ,MVM_CODEVE
        ,MVM_CODEG
        ,MVM_NUMEROSERIE
        ,MVM_LATITUDE
        ,MVM_LONGITUDE
        ,MVM_VELOCIDADE
        ,MVM_ODOMETRO
        ,MVM_IGNICAO
        ,MVM_TEMPERATURA
        ,MVM_DATAGPS
        ,MVM_HORAGPS
        ,MVM_TURNO
        ,MVM_HORIMETRO
        ,MVM_RPM
        ,MVM_ANOMES
        ,MVM_LOCALIZACAO) VALUES(
        @mvmPosicao       -- MVM_POSICAO
        ,@mvmCodVei       -- MVM_CODVEI
        ,@mvmPlaca        -- MVM_PLACA
        ,@mvmCodUni       -- MVM_CODUNI
        ,@mvmCodPol       -- MVM_CODPOL
        ,@mvmRfid         -- MVM_RFID
        ,@varCodMtr       -- MVM_CODMTR
        ,@mvmCodEveSS     -- MVM_CODEVESS
        ,@varCodEve       -- MVM_CODEVE
        ,@varCodEg        -- MVM_CODEG
        ,@mvmNumeroSerie  -- MVM_NUMEROSERIE
        ,@mvmLatitude     -- MVM_LATITUDE
        ,@mvmLongitude    -- MVM_LONGITUDE
        ,@mvmVelocidade   -- MVM_VELOCIDADE
        ,@mvmOdometro     -- MVM_ODOMETRO
        ,@mvmIgnicao      -- MVM_IGNICAO
        ,@mvmTemperatura  -- MVM_TEMPERATURA
        ,@mvmDataGps      -- MVM_DATAGPS
        ,@mvmHoraGps      -- MVM_HORAGPS
        ,@mvmTurno        -- MVM_TURNO
        ,@mvmHorimeto     -- MVM_HORIMETRO
        ,@mvmRpm          -- MVM_RPM
        ,@mvmAnoMes       -- MVM_ANOMES
        ,@mvmLocalizacao  -- MVM_LOCALIZACAO
      );
      -------------------------------------------------
      -- Gravando os eventos convertidos para historico
      -------------------------------------------------
      IF( @varCodEve=201 ) BEGIN
        INSERT INTO BI_ERRO(BIERR_POSICAO,BIERR_CODEG,BIERR_ANOMES) VALUES(@mvmPosicao,@varCodEgErr,@mvmAnoMes);
      END
      --
      --
      --------------------------------------
      -- Separando por evento EVENTOGRUPO
      -- AB   = Aceleracao brusca
      -- BV   = Bateria violada
      -- CB   = Conducao banguela
      -- ERPM = Excesso RPM
      -- EV   = Excesso de velocidade
      -- EVC  = Excesso de velocidade chuva
      -- FB   = Freada brusca
      -- VN   = Velocidade normalizada
      -------------------------------------
      IF( @varCodEg <> '*' ) BEGIN
        --------------------
        -- Aceleracao Brusca
        --------------------
        IF( @varCodEg='AB' ) BEGIN
          INSERT INTO BI_ACELERBRUSCA(
            BIAB_POSICAO  ,BIAB_DATAGPS ,BIAB_CODUNI  ,BIAB_CODMTR  ,BIAB_CODEVE  ,BIAB_CODVCL  ,BIAB_TURNO ,BIAB_ANOMES) VALUES(
            @mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblAbm=COALESCE(BIABM_ANOMES,0) FROM BI_ACELERBRUSCAMES
           WHERE ((BIABM_ANOMES=@mvmAnoMes) AND (BIABM_CODUNI=@mvmCodUni) AND (BIABM_CODMTR=@varCodMtr) 
             AND (BIABM_CODVCL=@varCodVcl) AND (BIABM_TURNO=@mvmTurno));  
          IF( @tblAbm=0 ) BEGIN
            INSERT INTO BI_ACELERBRUSCAMES(BIABM_ANOMES ,BIABM_CODUNI ,BIABM_CODMTR,BIABM_CODVCL,BIABM_TURNO,BIABM_TOTAL) VALUES(     
                                           @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno  ,1); 
          END ELSE BEGIN
            UPDATE BI_ACELERBRUSCAMES SET BIABM_TOTAL=(BIABM_TOTAL+1)
             WHERE ((BIABM_ANOMES=@mvmAnoMes) AND (BIABM_CODUNI=@mvmCodUni) AND (BIABM_CODMTR=@varCodMtr) 
               AND (BIABM_CODVCL=@varCodVcl) AND (BIABM_TURNO=@mvmTurno));  
          END
          -- Motorista
          UPDATE MOTORISTA SET MTR_BIAB=(MTR_BIAB+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
        END
        
        --------------------
        -- Bateria violada
        --------------------
        IF( @varCodEg='BV' ) BEGIN
          INSERT INTO BI_BATERIAVIOLA(
            BIBV_POSICAO  ,BIBV_DATAGPS ,BIBV_CODUNI  ,BIBV_CODMTR  ,BIBV_CODEVE  ,BIBV_CODVCL  ,BIBV_TURNO ,BIBV_ANOMES) VALUES(
            @mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblBvm=COALESCE(BIBVM_ANOMES,0) FROM BI_BATERIAVIOLAMES
           WHERE ((BIBVM_ANOMES=@mvmAnoMes) AND (BIBVM_CODUNI=@mvmCodUni) AND (BIBVM_CODMTR=@varCodMtr) 
             AND (BIBVM_CODVCL=@varCodVcl) AND (BIBVM_TURNO=@mvmTurno));  
          IF( @tblBvm=0 ) BEGIN
            INSERT INTO BI_BATERIAVIOLAMES(BIBVM_ANOMES ,BIBVM_CODUNI ,BIBVM_CODMTR,BIBVM_CODVCL,BIBVM_TURNO,BIBVM_TOTAL) VALUES(     
                                           @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno  ,1); 
          END ELSE BEGIN
            UPDATE BI_BATERIAVIOLAMES SET BIBVM_TOTAL=(BIBVM_TOTAL+1)
             WHERE ((BIBVM_ANOMES=@mvmAnoMes) AND (BIBVM_CODUNI=@mvmCodUni) AND (BIBVM_CODMTR=@varCodMtr) 
               AND (BIBVM_CODVCL=@varCodVcl) AND (BIBVM_TURNO=@mvmTurno));  
          END
        END
        ---------------------
        -- Conducao bangeuela
        ---------------------
        IF( @varCodEg='CB' ) BEGIN
				  --Devido erro de janeiro, em fev tirar IF
				  SET @varPosicao=0;
					SELECT @varPosicao=COALESCE(BICB_POSICAO,0) FROM BI_CONDUCAOBANG WHERE BICB_POSICAO=@mvmPosicao;
					IF( @@rowcount=0 ) BEGIN
				
						INSERT INTO BI_CONDUCAOBANG(
							BICB_POSICAO  ,BICB_DATAGPS ,BICB_CODUNI  ,BICB_CODMTR  ,BICB_CODEVE  ,BICB_CODVCL  ,BICB_TURNO ,BICB_ANOMES) VALUES(
							@mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
						);
						-- Mensal
						SELECT @tblCbm=COALESCE(BICBM_ANOMES,0) FROM BI_CONDUCAOBANGMES
						 WHERE ((BICBM_ANOMES=@mvmAnoMes) AND (BICBM_CODUNI=@mvmCodUni) AND (BICBM_CODMTR=@varCodMtr) 
							 AND (BICBM_CODVCL=@varCodVcl) AND (BICBM_TURNO=@mvmTurno));
						IF( @tblCbm=0 ) BEGIN
							INSERT INTO BI_CONDUCAOBANGMES(BICBM_ANOMES ,BICBM_CODUNI ,BICBM_CODMTR,BICBM_CODVCL,BICBM_TURNO,BICBM_TOTAL) VALUES(     
																						 @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno  ,1); 
						END ELSE BEGIN
							UPDATE BI_CONDUCAOBANGMES SET BICBM_TOTAL=(BICBM_TOTAL+1)
							 WHERE ((BICBM_ANOMES=@mvmAnoMes) AND (BICBM_CODUNI=@mvmCodUni) AND (BICBM_CODMTR=@varCodMtr) 
								 AND (BICBM_CODVCL=@varCodVcl) AND (BICBM_TURNO=@mvmTurno));  
						END
						-- Motorista
						UPDATE MOTORISTA SET MTR_BICB=(MTR_BICB+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
					END
        END
        ---------------------
        -- ERPM Alto
        ---------------------
        IF( @varCodEg='ERPM' ) BEGIN
          INSERT INTO BI_RPMALTO(
            BIRA_POSICAO  ,BIRA_DATAGPS ,BIRA_CODUNI  ,BIRA_CODMTR  ,BIRA_CODEVE  ,BIRA_CODVCL  ,BIRA_TURNO ,BIRA_ANOMES) VALUES(
            @mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblErpm=COALESCE(BIRAM_ANOMES,0) FROM BI_RPMALTOMES
           WHERE ((BIRAM_ANOMES=@mvmAnoMes) AND (BIRAM_CODUNI=@mvmCodUni) AND (BIRAM_CODMTR=@varCodMtr) 
             AND (BIRAM_CODVCL=@varCodVcl) AND (BIRAM_TURNO=@mvmTurno)); 
          IF( @tblErpm=0 ) BEGIN
            INSERT INTO BI_RPMALTOMES(BIRAM_ANOMES ,BIRAM_CODUNI ,BIRAM_CODMTR,BIRAM_CODVCL,BIRAM_TURNO ,BIRAM_TOTAL) VALUES(     
                                      @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno   ,1); 
          END ELSE BEGIN
            UPDATE BI_RPMALTOMES SET BIRAM_TOTAL=(BIRAM_TOTAL+1)
             WHERE ((BIRAM_ANOMES=@mvmAnoMes) AND (BIRAM_CODUNI=@mvmCodUni) AND (BIRAM_CODMTR=@varCodMtr) 
               AND (BIRAM_CODVCL=@varCodVcl) AND (BIRAM_TURNO=@mvmTurno));
          END
          -- Motorista
          UPDATE MOTORISTA SET MTR_BIERPM=(MTR_BIERPM+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
        END
        ---------------------
        -- Excesso velocidade
        ---------------------
        IF( @varCodEg='EV' ) BEGIN
          INSERT INTO BI_EXCESSOVELOC(
            BIEV_POSICAO  ,BIEV_DATAGPS ,BIEV_CODUNI  ,BIEV_CODMTR  ,BIEV_CODEVE  ,BIEV_CODVCL  ,BIEV_TURNO ,BIEV_ANOMES) VALUES(
            @mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblEvm=COALESCE(BIEVM_ANOMES,0) FROM BI_EXCESSOVELOCMES
           WHERE ((BIEVM_ANOMES=@mvmAnoMes) AND (BIEVM_CODUNI=@mvmCodUni) AND (BIEVM_CODMTR=@varCodMtr) 
             AND (BIEVM_CODVCL=@varCodVcl) AND (BIEVM_TURNO=@mvmTurno));
          IF( @tblEvm=0 ) BEGIN
            INSERT INTO BI_EXCESSOVELOCMES(BIEVM_ANOMES ,BIEVM_CODUNI ,BIEVM_CODMTR,BIEVM_CODVCL,BIEVM_TURNO,BIEVM_TOTAL) VALUES(     
                                           @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno  ,1); 
          END ELSE BEGIN
            UPDATE BI_EXCESSOVELOCMES SET BIEVM_TOTAL=(BIEVM_TOTAL+1)
             WHERE ((BIEVM_ANOMES=@mvmAnoMes) AND (BIEVM_CODUNI=@mvmCodUni) AND (BIEVM_CODMTR=@varCodMtr) 
               AND (BIEVM_CODVCL=@varCodVcl) AND (BIEVM_TURNO=@mvmTurno));
          END
          -- Motorista
          UPDATE MOTORISTA SET MTR_BIEV=(MTR_BIEV+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
        END
        ---------------------------
        -- Excesso velocidade chuva
        ---------------------------
        IF( @varCodEg='EVC' ) BEGIN
          INSERT INTO BI_EXCESSOVELCH(
            BIEVC_POSICAO  ,BIEVC_DATAGPS ,BIEVC_CODUNI  ,BIEVC_CODMTR  ,BIEVC_CODEVE  ,BIEVC_CODVCL  ,BIEVC_TURNO  ,BIEVC_ANOMES) VALUES(
            @mvmPosicao    ,@mvmDataGps   ,@mvmCodUni    ,@varCodMtr    ,@varCodEve    ,@varCodVcl    ,@mvmTurno    ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblEvcm=COALESCE(BIEVCM_ANOMES,0) FROM BI_EXCESSOVELCHMES
           WHERE ((BIEVCM_ANOMES=@mvmAnoMes) AND (BIEVCM_CODUNI=@mvmCodUni) AND (BIEVCM_CODMTR=@varCodMtr) 
             AND (BIEVCM_CODVCL=@varCodVcl) AND (BIEVCM_TURNO=@mvmTurno));
          IF( @tblEvcm=0 ) BEGIN
            INSERT INTO BI_EXCESSOVELCHMES(BIEVCM_ANOMES ,BIEVCM_CODUNI ,BIEVCM_CODMTR,BIEVCM_CODVCL,BIEVCM_TURNO ,BIEVCM_TOTAL) VALUES(     
                                           @mvmAnoMes    ,@mvmCodUni    ,@varCodMtr   ,@varCodVcl   ,@mvmTurno    ,1); 
          END ELSE BEGIN
            UPDATE BI_EXCESSOVELCHMES SET BIEVCM_TOTAL=(BIEVCM_TOTAL+1)
             WHERE ((BIEVCM_ANOMES=@mvmAnoMes) AND (BIEVCM_CODUNI=@mvmCodUni) AND (BIEVCM_CODMTR=@varCodMtr) 
               AND (BIEVCM_CODVCL=@varCodVcl) AND (BIEVCM_TURNO=@mvmTurno));
          END
          -- Motorista
          UPDATE MOTORISTA SET MTR_BIEVC=(MTR_BIEVC+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
        END
        ---------------------------
        -- Freada brusca
        ---------------------------
        IF( @varCodEg='FB' ) BEGIN
          INSERT INTO BI_FREADABRUSCA(
            BIFB_POSICAO  ,BIFB_DATAGPS ,BIFB_CODUNI  ,BIFB_CODMTR  ,BIFB_CODEVE  ,BIFB_CODVCL  ,BIFB_TURNO ,BIFB_ANOMES) VALUES(
            @mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblFbm=COALESCE(BIFBM_ANOMES,0) FROM BI_FREADABRUSCAMES
           WHERE ((BIFBM_ANOMES=@mvmAnoMes) AND (BIFBM_CODUNI=@mvmCodUni) AND (BIFBM_CODMTR=@varCodMtr) 
             AND (BIFBM_CODVCL=@varCodVcl) AND (BIFBM_TURNO=@mvmTurno));
          IF( @tblFbm=0 ) BEGIN
            INSERT INTO BI_FREADABRUSCAMES(BIFBM_ANOMES ,BIFBM_CODUNI ,BIFBM_CODMTR,BIFBM_CODVCL,BIFBM_TURNO,BIFBM_TOTAL) VALUES(     
                                           @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno  ,1); 
          END ELSE BEGIN
            UPDATE BI_FREADABRUSCAMES SET BIFBM_TOTAL=(BIFBM_TOTAL+1)
             WHERE ((BIFBM_ANOMES=@mvmAnoMes) AND (BIFBM_CODUNI=@mvmCodUni) AND (BIFBM_CODMTR=@varCodMtr) 
               AND (BIFBM_CODVCL=@varCodVcl) AND (BIFBM_TURNO=@mvmTurno));
          END
          -- Motorista
          UPDATE MOTORISTA SET MTR_BIFB=(MTR_BIFB+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
        END
        ----------------------------------------------
        -- Velocidade normalizada
        -- Retirado em 05jul2018 a pedido TRAC/CLIENTE
        ----------------------------------------------
        IF( @varCodEg='VN' ) BEGIN
          INSERT INTO BI_VELOCNORMALI(
            BIVN_POSICAO  ,BIVN_DATAGPS ,BIVN_CODUNI  ,BIVN_CODMTR  ,BIVN_CODEVE  ,BIVN_CODVCL  ,BIVN_TURNO ,BIVN_ANOMES) VALUES(
            @mvmPosicao   ,@mvmDataGps  ,@mvmCodUni   ,@varCodMtr   ,@varCodEve   ,@varCodVcl   ,@mvmTurno  ,@mvmAnoMes
          );
          -- Mensal
          SELECT @tblVnm=COALESCE(BIVNM_ANOMES,0) FROM BI_VELOCNORMALIMES
           WHERE ((BIVNM_ANOMES=@mvmAnoMes) AND (BIVNM_CODUNI=@mvmCodUni) AND (BIVNM_CODMTR=@varCodMtr) 
             AND (BIVNM_CODVCL=@varCodVcl) AND (BIVNM_TURNO=@mvmTurno));
          IF( @tblVnm=0 ) BEGIN
            INSERT INTO BI_VELOCNORMALIMES(BIVNM_ANOMES ,BIVNM_CODUNI ,BIVNM_CODMTR,BIVNM_CODVCL,BIVNM_TURNO,BIVNM_TOTAL) VALUES(     
                                           @mvmAnoMes   ,@mvmCodUni   ,@varCodMtr  ,@varCodVcl  ,@mvmTurno  ,1); 
          END ELSE BEGIN
            UPDATE BI_VELOCNORMALIMES SET BIVNM_TOTAL=(BIVNM_TOTAL+1)
             WHERE ((BIVNM_ANOMES=@mvmAnoMes) AND (BIVNM_CODUNI=@mvmCodUni) AND (BIVNM_CODMTR=@varCodMtr) 
               AND (BIVNM_CODVCL=@varCodVcl) AND (BIVNM_TURNO=@mvmTurno));
          END
          -- Motorista
          UPDATE MOTORISTA SET MTR_BIVN=(MTR_BIVN+1),MTR_BITOTAL=(MTR_BITOTAL+1),MTR_ATIVO='S' WHERE ((MTR_RFID=@mvmRfid) AND (MTR_ATIVO='S'));
        END
      END 
      ----------------------------------------------------------------------------------------------------
      -- Pegando o total de kilometragem por mes
      -- Como os registros naum estao em ordem pode ser que venha um odometro menor do que ja esta gravado
      ----------------------------------------------------------------------------------------------------
      IF( @mvmVelocidade>0 ) BEGIN
        SELECT @tblKmm=COALESCE(BIKMM_ANOMES,0),@odometroFim=COALESCE(BIKMM_ODOMETROFIM,0) FROM BI_KILOMETROMES
         WHERE ((BIKMM_ANOMES=@mvmAnoMes) AND (BIKMM_CODUNI=@mvmCodUni) AND (BIKMM_CODVCL=@varCodVcl) AND (BIKMM_TURNO=@mvmTurno));  
        IF( @tblKmm=0 ) BEGIN
          INSERT INTO BI_KILOMETROMES(BIKMM_ANOMES  ,BIKMM_CODUNI ,BIKMM_CODVCL,BIKMM_TURNO ,BIKMM_TOTAL,BIKMM_ODOMETROINI,BIKMM_ODOMETROFIM) VALUES(     
                                         @mvmAnoMes ,@mvmCodUni   ,@varCodVcl  ,@mvmTurno   ,0          ,@mvmOdometro     ,@mvmOdometro); 
        END ELSE BEGIN
          IF( @odometroFim<@mvmOdometro ) BEGIN
            UPDATE BI_KILOMETROMES 
               SET BIKMM_TOTAL=(@mvmOdometro-BIKMM_ODOMETROINI)
                   ,BIKMM_ODOMETROFIM=@mvmOdometro
             WHERE ((BIKMM_ANOMES=@mvmAnoMes) AND (BIKMM_CODUNI=@mvmCodUni) AND (BIKMM_CODVCL=@varCodVcl) AND (BIKMM_TURNO=@mvmTurno));         
          END
        END        
      END
      ----------------------------------------------------------
      -- Produtividade - prd
      -- Montorando o evento IGNICAO para pegar quando se altera
      ----------------------------------------------------------
      DECLARE @prdIgnicao INTEGER = 0;
      DECLARE @prdPosicao BIGINT = 0;
      DECLARE @prdDataGps DATETIME = NULL;
      DECLARE @prdCodUni INTEGER = 0;
      DECLARE @prdCodMtr INTEGER = 0;
      DECLARE @prdTurno VARCHAR(1) = '*';
      DECLARE @prdPlaca VARCHAR(10) = '*';
      DECLARE @prdOdometro NUMERIC(15,4) = 0;      
      DECLARE @prdAnoMes INTEGER = 0;
      -------------------------------------------------------------------  
      -- Procurando a placa(primary key) na tabela BI_PRODUTIVIDADESTATUS    
      -------------------------------------------------------------------
      SELECT @prdPlaca      = COALESCE(PS_PLACA,'*')
             ,@prdAnoMes    = PS_ANOMES
             ,@prdIgnicao   = PS_IGNICAO 
             ,@prdPosicao   = PS_POSICAO
             ,@prdDataGps   = PS_DATAGPS
             ,@prdCodUni    = PS_CODUNI
             ,@prdCodMtr    = PS_CODMTR
             ,@prdTurno     = PS_TURNO
             ,@prdOdometro  = PS_ODOMETRO
        FROM BI_PRODUTIVIDADESTATUS 
       WHERE PS_PLACA=@mvmPlaca AND PS_ANOMES=@mvmAnoMes;
      IF( (@prdPlaca='*') OR (@prdPlaca IS NULL) ) BEGIN
        ---------------------------------------------------------------------------------------
        -- Se naum achar apenas insiro na tabela para depois alternar PS_IGNICAO de 0->1 e 1->0
        ---------------------------------------------------------------------------------------
        INSERT INTO BI_PRODUTIVIDADESTATUS(
          PS_PLACA
          ,PS_ANOMES          
          ,PS_IGNICAO
          ,PS_POSICAO
          ,PS_DATAGPS
          ,PS_CODUNI
          ,PS_CODMTR
          ,PS_TURNO
          ,PS_ODOMETRO) VALUES(
          @mvmPlaca     -- PS_PLACA
          ,@mvmAnoMes   -- PS_ANOMES          
          ,@mvmIgnicao  -- PS_IGNICAO
          ,@mvmPosicao  -- PS_POSICAO
          ,@mvmDataGps  -- PS_DATAGPS
          ,@mvmCodUni   -- PS_CODUNI
          ,@varCodMtr   -- PS_CODMTR
          ,@mvmTurno    -- PS_TURNO
          ,@mvmOdometro -- PS_ODOMETRO
        );  
        
      END ELSE BEGIN 
        --------------------------------------------------------------------------------------------------------------
        -- Aqui tem um megaproblema conversado com Paulo/Pedro, as posicoes chegam com datas anteriores de ateh 4 dias
        -- Soh continuar se o odometro atual for maior que o ultimo encontrado
        --------------------------------------------------------------------------------------------------------------
        IF( @mvmOdometro > @prdOdometro ) BEGIN
          ----------------------------------------------------------------------------------------------
          -- Se achar e o status IGNICAO foi alterado
          -- Guardo o status antigo e o novo em BI_PRODUTIVIDADE
          -- Atualizo BI_PRODUTIVIDADESTATUS para o status atual para aguardar nova alteracao de IGNICAO
          ----------------------------------------------------------------------------------------------
          IF( @prdIgnicao <> @mvmIgnicao ) BEGIN
            DECLARE @prdRodando INTEGER   = 0;  
            DECLARE @prdParado INTEGER    = 0;
            DECLARE @erroGps VARCHAR(1)='N';
            IF( @prdIgnicao=0 ) BEGIN
              SET @prdParado=DATEDIFF(second,@prdDataGps,@mvmDataGps);
            END ELSE BEGIN
              SET @prdRodando=DATEDIFF(second,@prdDataGps,@mvmDataGps);
            END          
            ----------------------------------------
            -- Insere na alteracao do status IGNICAO
            ----------------------------------------
            INSERT INTO BI_PRODUTIVIDADE(
              BIPRD_CODUNI
              ,BIPRD_CODMTR
              ,BIPRD_CODVCL
              ,BIPRD_TURNO
              ,BIPRD_IGNICAOINI
              ,BIPRD_IGNICAOFIM
              ,BIPRD_IDINI
              ,BIPRD_IDFIM
              ,BIPRD_DATAGPSINI
              ,BIPRD_DATAGPSFIM
              ,BIPRD_TEMPORODANDO
              ,BIPRD_TEMPOPARADO
              ,BIPRD_ODOMETROINI
              ,BIPRD_ODOMETROFIM
              ,BIPRD_ODOMETRO
              ,BIPRD_ANOMES
              ,BIPRD_ERROGPS) VALUES(
              @prdCodUni                    -- BIPRD_CODUNI
              ,@prdCodMtr                   -- BIPRD_CODMTR
              ,@prdPlaca                    -- BIPRD_CODVCL
              ,@prdTurno                    -- BIPRD_TURNO
              ,@prdIgnicao                  -- BIPRD_IGNICAOINI
              ,@mvmIgnicao                  -- BIPRD_IGNICAOFIM
              ,@prdPosicao                  -- BIPRD_IDINI
              ,@mvmPosicao                  -- BIPRD_IDFIM
              ,@prdDataGps                  -- BIPRD_DATAGPSINI
              ,@mvmDataGps                  -- BIPRD_DATAGPSFIM
              ,@prdRodando                  -- BIPRD_TEMPORODANDO
              ,@prdParado                   -- BIPRD_TEMPOPARADO
              ,@prdOdometro                 -- BIPRD_ODOMETROINI
              ,@mvmOdometro                 -- BIPRD_ODOMETROFIM
              ,(@mvmOdometro-@prdOdometro)  -- BIPRD_ODOMETRO
              ,@prdAnoMes                   -- BIPRD_ANOMES
              ,@erroGps                     -- BIPRD_ERROGPS
            );
            DECLARE @pkProdutividade BIGINT=IDENT_CURRENT('BI_PRODUTIVIDADE');
            --
            --
            ----------------------------------------
            -- SUMARIZANDO POR MES (VEICULO)
            ----------------------------------------
            SELECT @tblPrdVm=COALESCE(BIPRDM_ANOMES,0)
              FROM BI_PRODUTIVIDADEVEIMES
             WHERE ((BIPRDM_ANOMES=@prdAnoMes) AND (BIPRDM_CODVCL=@prdPlaca));
            IF( (@tblPrdVm=0) OR (@tblPrdVm IS NULL) ) BEGIN
              INSERT INTO BI_PRODUTIVIDADEVEIMES(
                BIPRDM_ANOMES
                ,BIPRDM_CODVCL
                ,BIPRDM_TEMPORODANDO
                ,BIPRDM_TEMPOPARADO
                ,BIPRDM_ODOMETROINI
                ,BIPRDM_ODOMETROFIM
                ,BIPRDM_CODPRDINI           -- Para acelerar a busca quando solicitado detalhe do registro
                ,BIPRDM_CODPRDFIM) VALUES(  -- Para acelerar a busca quando solicitado detalhe do registro
                @prdAnoMes         -- BIPRDM_ANOMES
                ,@prdPlaca         -- BIPRDM_CODVCL
                ,@prdRodando       -- BIPRDM_TEMPORODANDO
                ,@prdParado        -- BIPRDM_TEMPOPARADO
                ,@prdOdometro      -- BIPRDM_ODOMETROINI
                ,@mvmOdometro      -- BIPRDM_ODOMETROFIM
                ,@pkProdutividade  -- BIPRDM_CODPRDINI
                ,@pkProdutividade  -- BIPRDM_CODPRDFIM
              );
            END ELSE BEGIN
              UPDATE BI_PRODUTIVIDADEVEIMES
                 SET BIPRDM_TEMPORODANDO=(BIPRDM_TEMPORODANDO+@prdRodando)
                     ,BIPRDM_TEMPOPARADO=(BIPRDM_TEMPOPARADO+@prdParado)
                     ,BIPRDM_ODOMETROFIM=@mvmOdometro
                     ,BIPRDM_CODPRDFIM=@pkProdutividade
               WHERE ((BIPRDM_ANOMES=@prdAnoMes) AND (BIPRDM_CODVCL=@prdPlaca));
            END
            --
            --
            ----------------------------------------
            -- SUMARIZANDO POR MES (MOTORISTA)
            ----------------------------------------
            SELECT @tblPrdMm=COALESCE(BIPRDM_ANOMES,0)
              FROM BI_PRODUTIVIDADEMOTMES
             WHERE ((BIPRDM_ANOMES=@prdAnoMes) AND (BIPRDM_CODMTR=@prdCodMtr));
            IF( (@tblPrdMm=0) OR (@tblPrdMm IS NULL) ) BEGIN
              INSERT INTO BI_PRODUTIVIDADEMOTMES(
                BIPRDM_ANOMES
                ,BIPRDM_CODMTR
                ,BIPRDM_TEMPORODANDO
                ,BIPRDM_TEMPOPARADO
                ,BIPRDM_TOTALKM
                ,BIPRDM_CODPRDINI           -- Para acelerar a busca quando solicitado detalhe do registro
                ,BIPRDM_CODPRDFIM) VALUES(  -- Para acelerar a busca quando solicitado detalhe do registro
                @prdAnoMes                   -- BIPRDM_ANOMES
                ,@prdCodMtr                  -- BIPRDM_CODMTR
                ,@prdRodando                 -- BIPRDM_TEMPORODANDO
                ,@prdParado                  -- BIPRDM_TEMPOPARADO
                ,(@mvmOdometro-@prdOdometro) -- BIPRDM_TOTALKM
                ,@pkProdutividade            -- BIPRDM_CODPRDINI
                ,@pkProdutividade            -- BIPRDM_CODPRDFIM
              );
            END ELSE BEGIN
              UPDATE BI_PRODUTIVIDADEMOTMES
                 SET BIPRDM_TEMPORODANDO=(BIPRDM_TEMPORODANDO+@prdRodando)
                     ,BIPRDM_TEMPOPARADO=(BIPRDM_TEMPOPARADO+@prdParado)
                     ,BIPRDM_TOTALKM=(BIPRDM_TOTALKM+(@mvmOdometro-@prdOdometro))
                     ,BIPRDM_CODPRDFIM=@pkProdutividade
               WHERE ((BIPRDM_ANOMES=@prdAnoMes) AND (BIPRDM_CODMTR=@prdCodMtr));
            END
            --
            --
            ----------------------------------------
            -- Atualiza para status atual
            ----------------------------------------
            UPDATE BI_PRODUTIVIDADESTATUS
               SET PS_IGNICAO   = @mvmIgnicao
                   ,PS_POSICAO  = @mvmPosicao
                   ,PS_DATAGPS  = @mvmDataGps
                   ,PS_CODUNI   = @mvmCodUni
                   ,PS_CODMTR   = @varCodMtr
                   ,PS_TURNO    = @mvmTurno
                   ,PS_ODOMETRO = @mvmOdometro
             WHERE ((PS_PLACA=@prdPlaca) AND (PS_ANOMES=@prdAnoMes));
          END -- IF( @prdIgnicao <> @mvmIgnicao ) BEGIN
        END --IF( @mvmOdometro>=@prdOdometro ) BEGIN
      END
      ----------------------------------------------------------
      -- Fim Produtividade
      ----------------------------------------------------------

      DECLARE @dataProximaConsolidacao DATETIME;

      SELECT @dataProximaConsolidacao = DATEADD(HOUR, INTERVALO_CONSOLIDACAO, DATA_CONSOLIDACAO)
	    FROM CONFIGURACAO_CONSOLIDACAO_INFRACAO;

    END
  END
END
go

