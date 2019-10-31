function converterData(data) {
  ano = data.substring(0, 4);
  mes = data.substring(5, 7);
  dia = data.substring(8, 10);
  hora = data.substring(11, 13);
  minuto = data.substring(14, 16);
  segundo = data.substring(17, 19);
  horaConvertida = hora + ":" + minuto + ":" + segundo;
  dataConvertida = dia + "/" + mes + "/" + ano;
  return { dataConvertida, horaConvertida };
}
