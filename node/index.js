const express = require("express");
const Web3 = require("web3");
const utils = require("ethereumjs-util");
const { signatureVerify, decodeAddress } = require("@polkadot/util-crypto");
const TronWeb = require("tronweb")
const app = express();
const port = 3000;

const { Trx } = TronWeb

app.use(express.json()); // for parsing application/json
app.use(express.urlencoded({ extended: true })); // for parsing application/x-www-form-urlencoded

app.post("/eth", async (req, res) => {
    let verify = false;
    try {
        const { sign, signMessage, address } = req.body;
        const r = utils.toBuffer(sign.slice(0, 66));
        const s = utils.toBuffer("0x" + sign.slice(66, 130));
        let v = "0x" + sign.slice(130, 132);
        v = Web3.utils.toDecimal(v);
        const m = Buffer.from(
            utils.hashPersonalMessage(Buffer.from(signMessage))
        );
        pub = utils.ecrecover(m, v, r, s);
        const signing_address = "0x" + utils.pubToAddress(pub).toString("hex");

        verify = signing_address == address;
        console.log("ETH", verify, sign, signMessage, address);
    } catch (e) {
        console.error(e);
    }

    res.send({ verify });
});

app.post("/dot", async (req, res) => {
    let verify = false;
    try {
        const { sign, signMessage, address } = req.body;
        const publicKey = decodeAddress(address, 0);
        const verification = signatureVerify(signMessage, sign, publicKey);

        verify = verification.isValid;
        console.log("DOT", verify, sign, signMessage, address);
    } catch (e) {
        console.error(e);
    }

    res.send({ verify });
});

app.post("/tron", async (req, res) => {
    let verify = false
    try {
        const { sign, signMessage, address } = req.body;
        const message = TronWeb.toHex(signMessage);
        verify = Trx.verifySignature(message, address, sign);
    } catch (e) {
        console.error(e);
    }
    res.send({ verify });
})





app.listen(port, () => {
    console.log(`Example app listening at http://localhost:${port}`);
});
