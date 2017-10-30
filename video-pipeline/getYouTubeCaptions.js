//Currently uses keys on stephenhuh@gmail.com account
//https://developers.google.com/identity/protocols/OAuth2ServiceAccount
const google = require('googleapis');
const rp = require ('request-promise');
const fetch = require('node-fetch');
const util = require('util');
const fs = require('fs');
const youtube = google.youtube('v3');

const inspectJSON = function(object) {
  console.log(util.inspect(object, {showHidden: false, depth: null}));
}

//OAuth 2.0
function authenticate () {
  
}


const API_KEY = 'AIzaSyD_vZ3xxGoO-o6juGvHEDatbQyjnx81euc';

async function getYouTubeCaptions() {
  try {
    const result = await fetch(`https://www.googleapis.com/youtube/v3/captions?videoId=568g8hxJJp4&part=snippet&key=${API_KEY}`);
    const subtitles = await result.json();
    inspectJSON(subtitles);
    const subtitleIdToDownload = subtitles.items[0].id;
    //How to use a stream instead?
    //TODO: use access tokens and OAuth 2.0 i think is required to download captions here
    const res = await fetch(`https://www.googleapis.com/youtube/v3/captions/${subtitleIdToDownload}?key=${API_KEY}`);
    const dest = fs.createWriteStream('./file');

    res.body.pipe(dest);
  } catch (error) {
    console.warn(error);
  }
}

getYouTubeCaptions();



