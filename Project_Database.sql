CREATE DATABASE stock_portfolio;
USE stock_portfolio;

CREATE TABLE users (
    username VARCHAR(100) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    firstname VARCHAR(100),
    lastname VARCHAR(100)
);

CREATE TABLE bank (
    bankid INT IDENTITY(1,1) PRIMARY KEY,
    bank_name VARCHAR(255) NOT NULL,
    bank_shortname VARCHAR(10) NOT NULL
);

CREATE TABLE bank_account (
    account_number VARCHAR(50) PRIMARY KEY,
    bankid INT,
    FOREIGN KEY (bankid) REFERENCES bank(bankid) ON DELETE CASCADE
);

CREATE TABLE broker (
    brokerid INT IDENTITY(1,1) PRIMARY KEY,
    broker_name VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE portfolio (
    portid VARCHAR(50) PRIMARY KEY,
    username VARCHAR(100),
    brokerid INT,
    account_number VARCHAR(50),
    balance DECIMAL(10,2) DEFAULT 0 NOT NULL,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (brokerid) REFERENCES broker(brokerid) ON DELETE SET NULL,
    FOREIGN KEY (account_number) REFERENCES bank_account(account_number) ON DELETE SET NULL
);

CREATE TABLE bank_transaction (
    bank_transacid INT IDENTITY(1,1) PRIMARY KEY,
    portid VARCHAR(50),
    account_number VARCHAR(50),
    transaction_type VARCHAR(10) CHECK (transaction_type IN ('DEPOSIT', 'WITHDRAW')) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_date DATE DEFAULT GETDATE(),
    FOREIGN KEY (portid) REFERENCES portfolio(portid) ON DELETE CASCADE,
    FOREIGN KEY (account_number) REFERENCES bank_account(account_number) ON DELETE CASCADE
);

CREATE TABLE stock (
    stockid INT IDENTITY(1,1) PRIMARY KEY,
    symbol VARCHAR(10) UNIQUE NOT NULL,
    company_name VARCHAR(255) NOT NULL
);

CREATE TABLE stock_lot (
    lotid INT IDENTITY(1,1) PRIMARY KEY,
    portid VARCHAR(50),
    stockid INT,
    trade_style VARCHAR(20) NOT NULL,
    quantity INT NOT NULL,
    average_buy_price DECIMAL(10,2) NOT NULL,
    total_invested DECIMAL(10,2) NOT NULL,
    buy_date DATE DEFAULT GETDATE(),
    sell_date DATE,
    profit_loss DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(10) CHECK (status IN ('OPEN', 'CLOSED')) NOT NULL DEFAULT 'OPEN',
    FOREIGN KEY (portid) REFERENCES portfolio(portid) ON DELETE CASCADE,
    FOREIGN KEY (stockid) REFERENCES stock(stockid) ON DELETE CASCADE
);

CREATE TABLE stock_transaction (
    transacid INT IDENTITY(1,1) PRIMARY KEY,
    portid VARCHAR(50),
    stockid INT,
    trade_style VARCHAR(20) NOT NULL,
    transaction_type VARCHAR(10) CHECK (transaction_type IN ('BUY', 'SELL')) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    transaction_date DATE DEFAULT GETDATE(),
    FOREIGN KEY (portid) REFERENCES portfolio(portid) ON DELETE CASCADE,
    FOREIGN KEY (stockid) REFERENCES stock(stockid) ON DELETE CASCADE
);
