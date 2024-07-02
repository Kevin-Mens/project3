CREATE TABLE Pokemon (
    dexid INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type1 VARCHAR(20) NOT NULL,
    type2 VARCHAR(20),
    statTotal INT NOT NULL,
    hp INT NOT NULL,
    atk INT NOT NULL,
    def INT NOT NULL,
    spatk INT NOT NULL,
    spdef INT NOT NULL,
    spd INT NOT NULL,
    gen INT NOT NULL,
    legendary BOOLEAN NOT NULL
);

CREATE TABLE Player (
    pid INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    money INT NOT NULL,
    highestTrainer INT NOT NULL
);

CREATE TABLE PlayerParty (
    pid INT PRIMARY KEY,
    catchId1 INT,
    catchId2 INT,
    catchId3 INT,
    catchId4 INT,
    catchId5 INT,
    catchId6 INT,
    FOREIGN KEY (pid) REFERENCES Player(pid)
);

CREATE TABLE PlayerCollection (
    pid INT,
    catchId INT PRIMARY KEY AUTO_INCREMENT,
    dexid INT,
    FOREIGN KEY (pid) REFERENCES Player(pid),
    FOREIGN KEY (dexid) REFERENCES Pokemon(dexid)
);