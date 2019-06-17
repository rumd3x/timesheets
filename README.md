# timesheets
Timesheet automation.

# To Do
- Adicionar tela de configuracao da aplicao (Settings) -> Subir XLS de template, indicar coluna e linha inicial, configurar quantidade de horas target, configurar emails para receber a planilha target e a planilha de horas reais.

- Adicionar Resumo de Horas Totais na (Visao Mensal)

- Job de Sanitizacao (Verificar existencia de duas ou mais entradas ou saidas consecutivas [usando timestamp ORDER BY moment e ver se a flag entry eh igual], e manter apenas uma).

- Job de Sanitizacao 2 (Verificar existencia de entrada sem saida e saida sem entrada e trata-las)

- Job para Gerar Planilha de Horas Target e Horas Reais e enviar para os emails configurados
