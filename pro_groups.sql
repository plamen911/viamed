BEGIN TRANSACTION;
DROP TABLE IF EXISTS "pro_groups";
CREATE TABLE "pro_groups" ("id" INTEGER PRIMARY KEY  NOT NULL ,"parent" INTEGER DEFAULT (0) ,"name" VARCHAR,"num" INTEGER DEFAULT (0));
INSERT INTO "pro_groups" VALUES(1,0,'ЦДГ и ОДЗ',0);
INSERT INTO "pro_groups" VALUES(2,1,'Детски учител, помощник възпитател,педагог, детегледачка, бавачка',1);
INSERT INTO "pro_groups" VALUES(3,1,'Директор, ЗАТС, домакин, счетоводител',2);
INSERT INTO "pro_groups" VALUES(4,1,'Чистач, готвач, перач, прислужник, работник в кухня',3);
INSERT INTO "pro_groups" VALUES(5,1,'Медицинска сестра',4);
INSERT INTO "pro_groups" VALUES(6,1,'Оператор парни и водогрейни съоражения,ел.техник, дърводелец',5);
INSERT INTO "pro_groups" VALUES(7,0,'ШИВАШКА ПРОМИШЛЕНОСТ',0);
INSERT INTO "pro_groups" VALUES(8,7,'Машинен оператор',1);
INSERT INTO "pro_groups" VALUES(9,7,'Гладач',2);
INSERT INTO "pro_groups" VALUES(10,7,'Ръчник',3);
INSERT INTO "pro_groups" VALUES(11,7,'Опаковка',4);
INSERT INTO "pro_groups" VALUES(12,7,'Крояч',5);
INSERT INTO "pro_groups" VALUES(13,7,'ОТК, бригадир,контрола,окачествител, технолог',6);
INSERT INTO "pro_groups" VALUES(14,7,'Управител,технически секретар,счетоводител',7);
INSERT INTO "pro_groups" VALUES(15,7,'Машинен техник, охрана',8);
INSERT INTO "pro_groups" VALUES(16,0,'МЕТАЛООБРАБОТВАНЕ',0);
INSERT INTO "pro_groups" VALUES(17,16,'Заварчик,газорезач, оксиженист',1);
INSERT INTO "pro_groups" VALUES(18,16,'Мениджър, управител, технически организатор',2);
INSERT INTO "pro_groups" VALUES(19,16,'Шлосер, стругар, разкройчик,фрезист, матричар, шлайфист, механик, ел.техник',3);
INSERT INTO "pro_groups" VALUES(20,16,'Шофьор',4);
INSERT INTO "pro_groups" VALUES(21,16,'Общ работник',5);
INSERT INTO "pro_groups" VALUES(22,0,'СТРОИТЕЛСТВО',0);
INSERT INTO "pro_groups" VALUES(23,22,'Управител,гл.инженер,проектант, гл.счетоводител, строителен специалист, домакин,техник, строителство и архитектура, секретар',1);
INSERT INTO "pro_groups" VALUES(24,22,'Зидаро-мазач, строителен работник, монтажист, зидаро-кофражист, грейдерист, арматурист, общ работник, технически изпълнител',2);
INSERT INTO "pro_groups" VALUES(25,22,'Автомонтьор',3);
INSERT INTO "pro_groups" VALUES(26,22,'Снабдител, пом.снабдител, шофьор товарен автомобил, шофьор автобус, багерист, кранист, водач открита платформа',4);
INSERT INTO "pro_groups" VALUES(27,22,'Бояджия, ел.техник,водопроводчик, електромонтьор',5);
INSERT INTO "pro_groups" VALUES(28,22,'Охрана',6);
INSERT INTO "pro_groups" VALUES(29,0,'УЧИЛИЩА',0);
INSERT INTO "pro_groups" VALUES(30,29,'Старши учител, учител, старши възпитател, възпитател, учител психолог, логопед,педагогически съветник',1);
INSERT INTO "pro_groups" VALUES(31,29,'Директор, помощник директор АСД, ЗАТС, библиотекар, домакин, касиер, счетоводител',2);
INSERT INTO "pro_groups" VALUES(32,29,'Общ работник, оператор парни и водогрейни съоражения,работник поддръжка, чистач, ел.техник',3);
INSERT INTO "pro_groups" VALUES(33,0,'ОБУВНА ПРОМИШЛЕНОСТ',0);
INSERT INTO "pro_groups" VALUES(34,33,'Крояч изделия от кожа,моделиер кожни изделия',1);
INSERT INTO "pro_groups" VALUES(35,33,'Ръчник, подготвител горница обувки, монтажник изделия от кожа',2);
INSERT INTO "pro_groups" VALUES(36,33,'Машинен оператор обувно производство',3);
INSERT INTO "pro_groups" VALUES(37,33,'Чистач',4);
INSERT INTO "pro_groups" VALUES(38,33,'Административен управител, склададжия, бригадир, контрольор',5);
INSERT INTO "pro_groups" VALUES(39,33,'Техник-механик',6);
INSERT INTO "pro_groups" VALUES(40,0,'ТЪРГОВИЯ НА ЕДРО И ДРЕБНО',0);
INSERT INTO "pro_groups" VALUES(41,40,'Управител,гл.счетоводител, счетоводител, секретар, касиер, офис сътрудник,аранжор-витрини',1);
INSERT INTO "pro_groups" VALUES(42,40,'Ел.монтьор, шофьор, шлосер, оператор ксерокс, оператор парни и водогрейни съоражения',2);
INSERT INTO "pro_groups" VALUES(43,40,'Портиер, общ работник, хигиенист',3);
INSERT INTO "pro_groups" VALUES(44,40,'Продавач-консултант',4);
INSERT INTO "pro_groups" VALUES(45,40,'Склададжия',5);
INSERT INTO "pro_groups" VALUES(46,0,'В и К',0);
INSERT INTO "pro_groups" VALUES(47,46,'Шофьор на товарен автомобил, шофьор на МПС до 9 места, багерист,автомонтьор, механик гараж за транспортни средства, машинист на еднокофов багер',1);
INSERT INTO "pro_groups" VALUES(48,46,'Водопроводчик на външен водопровод, оператор на ВПС и хлоратор,работник по водопровод и поддръжка, каналджия, общ работник',2);
INSERT INTO "pro_groups" VALUES(49,46,'Управител, началник строителен обект и гл.инженер, главен счетоводител, счетоводител, касиер счетоводител, юристконсулт, технически секретар, еколог-мониторинг на питейни води, специалист кадастър и регулация,технически ръководител, зав.личен състав,ръководител ЕМТ, ръководител участък ПСОВ,химик аналитик, лаборант;ръководител направление ЗБУТ, оператор на компютъ',3);
INSERT INTO "pro_groups" VALUES(50,46,'Ел.техник, шлосер-електрозаварчик, ел.монтьор ПСОВ, оператор ПСОВ, монтьор, шлосер-монтьор',4);
INSERT INTO "pro_groups" VALUES(51,46,'Инкасатори събирач на данъци и такси, инкасатор дългови задължения,специалист контрол приходи, снабдител-доставчик',5);
INSERT INTO "pro_groups" VALUES(52,46,'Чистач, пазач невъоръжена охрана, пазач водохващане',6);
INSERT INTO "pro_groups" VALUES(53,0,'ХОТЕЛ И РЕСТОРАНТ',0);
INSERT INTO "pro_groups" VALUES(54,53,'Управител,администратор',1);
INSERT INTO "pro_groups" VALUES(55,53,'Сервитьор, барман',2);
INSERT INTO "pro_groups" VALUES(56,53,'Гл.готвач, помощник готвач, работник в кухня',3);
INSERT INTO "pro_groups" VALUES(57,53,'Камериер',4);
INSERT INTO "pro_groups" VALUES(58,53,'Портиер, пиколо',5);
INSERT INTO "pro_groups" VALUES(59,53,'Музикален оформител',6);
INSERT INTO "pro_groups" VALUES(60,0,'СЛАДКАРСКИ ЦЕХ',0);
INSERT INTO "pro_groups" VALUES(61,60,'Сладкар, майстор сладкар,пом сладкар, работник смесване, работник размерване, оператор на машина',1);
INSERT INTO "pro_groups" VALUES(62,60,'Работник в склад',2);
INSERT INTO "pro_groups" VALUES(63,60,'Пласьор, продавач разносна търговия, продавач консултант, търговски представител',3);
INSERT INTO "pro_groups" VALUES(64,60,'Пакетировач',4);
INSERT INTO "pro_groups" VALUES(65,60,'Хигиенист',5);
INSERT INTO "pro_groups" VALUES(66,60,'Завеждащ личен състав, офис сътрудник,инженер технолог,екперт връзки с обществеността,оператор компютър',6);
INSERT INTO "pro_groups" VALUES(67,60,'Шофьор, електромонтьор, ел.техник,работник поддръжка',7);
INSERT INTO "pro_groups" VALUES(68,0,'МЕДИЦИНСКИ ЦЕНТЪР',0);
INSERT INTO "pro_groups" VALUES(69,68,'Управител, завеждащ  отделение, лекар',1);
INSERT INTO "pro_groups" VALUES(70,68,'Медицинска сестра, старша мед.сестра, мед.фелдшер, кинезитерапевт, рехабилитатор',2);
INSERT INTO "pro_groups" VALUES(71,68,'Гл.счетоводител, счетоводител, завеждащ личен състав, зав техническа служба, касиер,управител на склад',3);
INSERT INTO "pro_groups" VALUES(72,68,'Санитар, общ работник',4);
INSERT INTO "pro_groups" VALUES(73,68,'Работник по озеленяване, пазач',5);
INSERT INTO "pro_groups" VALUES(74,0,'СПА ЦЕНТЪР',0);
INSERT INTO "pro_groups" VALUES(75,74,'Управител,мениджър продажби',1);
INSERT INTO "pro_groups" VALUES(76,74,'Масажист, козметик',2);
INSERT INTO "pro_groups" VALUES(77,74,'Спасител, спортен инструктор, шофьор',3);
INSERT INTO "pro_groups" VALUES(78,74,'Хигиенист',4);
INSERT INTO "pro_groups" VALUES(79,0,'МЕБЕЛНА ПРОМИШЛЕНОСТ',0);
INSERT INTO "pro_groups" VALUES(80,79,'Управител,началник цех гл.счетоводител, зам гл.счетоводител, управител склад, зав личен състав, отчетник на материали, организатор производство, моделиер,търговски представител, технически организатор',1);
INSERT INTO "pro_groups" VALUES(81,79,'Оператор дървообработваща машина',2);
INSERT INTO "pro_groups" VALUES(82,79,'Оператор шевна машина',3);
INSERT INTO "pro_groups" VALUES(83,79,'Производител на мека мебел,тапицер,ковач на ластици',4);
INSERT INTO "pro_groups" VALUES(84,79,'Крояч на кожа, крояч на плат',5);
INSERT INTO "pro_groups" VALUES(85,79,'Опаковка,общ работник',6);
INSERT INTO "pro_groups" VALUES(86,79,'Монтьор ремонт на машини, хигиенист',7);
INSERT INTO "pro_groups" VALUES(87,0,'ОБЩИНА',0);
INSERT INTO "pro_groups" VALUES(88,87,'Кмет, зам. кмет, секретар на община, старши вътрешен одитор',1);
INSERT INTO "pro_groups" VALUES(89,87,'Общинска администрация - директор на дирекция, главен юрисконсулт, старши експерт, младши експерт, главен специалист, секретар на комисия, главен архитект, касиер, технически сътрудник',2);
INSERT INTO "pro_groups" VALUES(90,87,'Старши специалист „Гражданска защита и ОМП”, дежурни по ГО, техник на пунктове за управление',3);
INSERT INTO "pro_groups" VALUES(91,87,'Мед.сестра (детска ясла), мед сестра (училищно здравеопазване), здравен медиатор, санитар',4);
INSERT INTO "pro_groups" VALUES(92,87,'Главен готвач, помощник готвач, разносвач на храна,снабдител',5);
INSERT INTO "pro_groups" VALUES(93,87,'Шофьор',6);
INSERT INTO "pro_groups" VALUES(94,87,'Горски работник, озеленител, полски пазач',7);
INSERT INTO "pro_groups" VALUES(95,87,'Сметосъбирач, общ работник',8);
INSERT INTO "pro_groups" VALUES(96,0,'ПРОИЗВОДСТВО НА ТОАЛЕТНА ХАРТИЯ И САЛФЕТКИ',0);
INSERT INTO "pro_groups" VALUES(97,96,'Управител, счетоводител, търговски сътрудник,отчетник водещ документи, организатор производство и планиране, финансово-счетоводен персонал,ръководител отдел маркетинг,архивист офис, търговски представител, търговски агент',1);
INSERT INTO "pro_groups" VALUES(98,96,'Машинен оператор производство на хартия, пакетировач',2);
INSERT INTO "pro_groups" VALUES(99,96,'Водач на товарен автомобил, механик промишлено оборудване, мотокарист',3);
INSERT INTO "pro_groups" VALUES(100,96,'Продавач-консултант, продавач в разносна търговия, специалист доставки, експедитор стоки и товар',4);
INSERT INTO "pro_groups" VALUES(101,96,'Чистач производствени помещения, общ работник в промишлеността, пазач',5);
INSERT INTO "pro_groups" VALUES(102,96,'Контрольор качество',6);
INSERT INTO "pro_groups" VALUES(103,0,'ПРОИЗВОДСТВО НА ПЛСМАСОВИ ИЗДЕЛИЯ',0);
INSERT INTO "pro_groups" VALUES(104,103,'Управител, началник цех, търговски представител, експерт, счетоводител, склададжия, специалист маркетинг, зам директор, зав административна служба, технолог, касиер-счетоводител, организатор по труда, гл счетоводител, стоковед,организатор смяна, домакин на почивна база,магазинер ФМ',1);
INSERT INTO "pro_groups" VALUES(105,103,'Монтажник изделия от пласмаса,настройчик машини, матричар,моделиер конструктор,щанцьор ИЩФ,екструдерист, оператор гранул. линия, обрезвач пл.отпадъци, конфекционер',2);
INSERT INTO "pro_groups" VALUES(106,103,'Водач лек автомобил, водач автобус,водач мотокар, багерист,водач електрокар',3);
INSERT INTO "pro_groups" VALUES(107,103,'Чистач, пазач въоражена охрана, общ работник',4);
INSERT INTO "pro_groups" VALUES(108,103,'Контрольор ТКК',5);
INSERT INTO "pro_groups" VALUES(109,103,'Опаковач, балировач-пакетировач',6);
INSERT INTO "pro_groups" VALUES(110,103,'Техник–механик, електроерозист, стругар, матричар, шлайфист, бобиньор, електромонтьор, дърводелец, строителен работник',7);
INSERT INTO "pro_groups" VALUES(111,103,'Главен готвач, готвач, работник в кухня',8);
INSERT INTO "pro_groups" VALUES(112,0,'ТРАНСПОРТ',0);
INSERT INTO "pro_groups" VALUES(113,112,'Икономически директор, управител, началник автотранспорт, управител Логистичен център, касиер-счетоводител, личен състав, счетоводител, технически сътрудник, технически секретар, отчетник финанси, контрольор–приемчик, спедиционен посредник, сътрудник продажби, офис организатор',1);
INSERT INTO "pro_groups" VALUES(114,112,'Шофьор, електротехник',2);
INSERT INTO "pro_groups" VALUES(115,112,'Портиер, пазач',3);
INSERT INTO "pro_groups" VALUES(116,112,'Общ работник, монтьор',4);
INSERT INTO "pro_groups" VALUES(117,0,'СЪД',0);
INSERT INTO "pro_groups" VALUES(118,117,'Председател РС,Районен съдия, съдия по вписванията, държавен съдия изпълнител',1);
INSERT INTO "pro_groups" VALUES(119,117,'Административен секретар, гл.счетоводител, завеждащ административна служба, съдебен секретар-протоколист, съдебен деловодител,съдебен архивар, системен администратор',2);
INSERT INTO "pro_groups" VALUES(120,117,'Призовкар, огняр, чистач (прислужник)',3);
INSERT INTO "pro_groups" VALUES(121,0,'ДЪРВООБРАБОТВАЩА',0);
INSERT INTO "pro_groups" VALUES(122,121,'Административен ръководител, ръководител транспорт, счетоводител, домакин',1);
INSERT INTO "pro_groups" VALUES(123,121,'Оператор на банциг, машинен оператор, точилар, гатерист, дърводелец,ел техник',2);
INSERT INTO "pro_groups" VALUES(124,121,'Водач на товарен автомобил, мотокарист',3);
INSERT INTO "pro_groups" VALUES(125,121,'Сортировач,общ работник',4);
INSERT INTO "pro_groups" VALUES(126,0,'SOS ДЕТСКО СЕЛИЩЕ',0);
INSERT INTO "pro_groups" VALUES(127,126,'SOS-майки, SOS-помощник майки',1);
INSERT INTO "pro_groups" VALUES(128,126,'старши възпитател, възпитател, педагог, психолог',2);
INSERT INTO "pro_groups" VALUES(129,126,'директор, ръководител младежки дом, счетоводител, касиер-секретар',3);
INSERT INTO "pro_groups" VALUES(130,126,'социален работник, сътрудник социални дейности',4);
INSERT INTO "pro_groups" VALUES(131,126,'градинар, поддръжка, селищен майстор,хигенист, шофьор',5);
INSERT INTO "pro_groups" VALUES(132,0,'КАЗИНА',0);
INSERT INTO "pro_groups" VALUES(133,132,'Пит Бос, управител, управител търговска зала, контрольор регистр',1);
INSERT INTO "pro_groups" VALUES(134,132,'Крупие,гейм мениджър',2);
INSERT INTO "pro_groups" VALUES(135,132,'Касиер,финансов мениджър',3);
INSERT INTO "pro_groups" VALUES(136,132,'Сервитьор, барман',4);
INSERT INTO "pro_groups" VALUES(137,132,'Готвач',5);
INSERT INTO "pro_groups" VALUES(138,132,'Хигиенист',6);
INSERT INTO "pro_groups" VALUES(139,0,'КОЖАРСКА ПРОМИШЛЕНОСТ',0);
INSERT INTO "pro_groups" VALUES(140,139,'кожарски работник суха обработка, кожарски работник мокра обработка, оператор пречиствателна станция',1);
INSERT INTO "pro_groups" VALUES(141,139,'административен персонал',2);
INSERT INTO "pro_groups" VALUES(142,139,'магазинар',3);
INSERT INTO "pro_groups" VALUES(143,139,'строител, шофьор, огняр, охрана',4);
INSERT INTO "pro_groups" VALUES(144,0,'ХЛЕБНА ПРОМИШЛЕНОСТ',0);
INSERT INTO "pro_groups" VALUES(145,144,'управител, главен счетоводител, технолог, експедитор, оператор на компютър',1);
INSERT INTO "pro_groups" VALUES(146,144,'формировач, пекар, производство ръчен хляб, месач, сладкар, резач, опаковач',2);
INSERT INTO "pro_groups" VALUES(147,144,'редач на хляб, товарач, пласьор, шофьор',3);
INSERT INTO "pro_groups" VALUES(148,144,'машинен монтьор, ел.техник',4);
INSERT INTO "pro_groups" VALUES(149,144,'огняр, чистач, охрана',5);
INSERT INTO "pro_groups" VALUES(150,0,'СТРОИТЕЛЕН  НАДЗОР',0);
INSERT INTO "pro_groups" VALUES(151,150,'управител, главен инжинер,главен специалист,касиер',1);
INSERT INTO "pro_groups" VALUES(152,150,'контролен специалист, инвестиционен контрол, експерт',2);
INSERT INTO "pro_groups" VALUES(153,150,'шофьор',3);
INSERT INTO "pro_groups" VALUES(154,0,'ПРОИЗВОДСТВО НА ЦИПОВЕ',0);
INSERT INTO "pro_groups" VALUES(155,154,'работник сглобяване на детайли, сновач, тъкач, секционен майстор',1);
INSERT INTO "pro_groups" VALUES(156,154,'управител СГП, ръководител направление ГП, технически секретар, организатор маркетинг, специалист по качество, завеждащ личен състав, гл.счетоводител, касиер-счетоводител, счетоводител, организатор обработка на производствена информация,организатор по ремонт и поддръжка',2);
INSERT INTO "pro_groups" VALUES(157,154,'леяр, машинен оператор, екструдерист, апретурист, байцвач, шлосер, шлайфист, стругар водопроводчик, ел.техник, огняр',3);
INSERT INTO "pro_groups" VALUES(158,154,'пазач, чистач',4);
INSERT INTO "pro_groups" VALUES(159,0,'ЛЕСНИЧЕЙСТВО',0);
INSERT INTO "pro_groups" VALUES(160,159,'горски работник, секач, работник почистващ сечище, общ работник',1);
INSERT INTO "pro_groups" VALUES(161,159,'банцигар, монтьор на дървообработващи машини',2);
INSERT INTO "pro_groups" VALUES(162,159,'инжинер лесовъд, организатор експедиция, оператор компютър, секретар',3);
INSERT INTO "pro_groups" VALUES(163,159,'тракторист, шофьор, машинен оператор',4);
INSERT INTO "pro_groups" VALUES(164,159,'ловен надзирател, горски надзирател',5);
INSERT INTO "pro_groups" VALUES(165,0,'ПРОИЗВОДСТВО НА ДОГРАМИ',0);
INSERT INTO "pro_groups" VALUES(166,165,'дограмаджия, монтажник',1);
INSERT INTO "pro_groups" VALUES(167,165,'управител, началник склад, търговски представител, организатор производство',2);
INSERT INTO "pro_groups" VALUES(168,165,'работник сглобяване на детайли, стъклар, стъкломияч',3);
INSERT INTO "pro_groups" VALUES(169,165,'шлосер',4);
COMMIT;
