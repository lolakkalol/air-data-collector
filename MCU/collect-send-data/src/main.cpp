#include <Arduino.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include "MHZ19B.h"
#include "PMS5003T.h"
#include "ScioSense_ENS160.h"
#include "AHTxx.h"

// These use software serial and needs to be initilized after
MHZ19B*   mhz19b;
PMS5003T* pms5003t;

// These use the I2C perif and can be initilized now
ScioSense_ENS160 ens160(ENS160_I2CADDR_1);
AHTxx            aht2x(AHTXX_ADDRESS_X38, AHT2x_SENSOR);

// Network strings
static String ssid = "SSID";
static String password = "PASSWORD";

static String hostname = "https://ADDRESS.COM/insert_data.php";

const char* root_ca= "your root certificate";

void setup() {
  /*********************************/
  /*          Serial setup         */
  /*********************************/
  Serial.begin(115200);
  while(!Serial) {}

  /*********************************/
  /*          MHZ19b setup         */
  /*********************************/
  mhz19b   = new MHZ19B(33, 32);   //Rx, Tx
  Serial.println("MHZ19b initilization... done.");

  Serial.print("\tSetting auto calibration of zero point ");
  Serial.println(mhz19b->calibrate_zero_point_auto(true) == MHZ19B_STATUS::OK ? "done." : "failed!");

  /*********************************/
  /*         PMS5003T setup        */
  /*********************************/
  pms5003t = new PMS5003T(26, 25); //Rx, Tx
  Serial.println("PMS5003T initilization... done.");

  Serial.print("\tSetting passive mode true ");
  Serial.println(pms5003t->passive_mode(true) == PMS5003T_STATUS::OK ? "done." : "failed!");

  /*********************************/
  /*          ENS160 setup         */
  /*********************************/
  Serial.print("ENS160 initilization...");
  ens160.begin();
  Serial.println(ens160.available() ? "done." : "failed!");

  if (ens160.available()) {
    // Print ENS160 versions
    Serial.print("\tRev: "); Serial.print(ens160.getMajorRev());
    Serial.print("."); Serial.print(ens160.getMinorRev());
    Serial.print("."); Serial.println(ens160.getBuild());
  
    Serial.print("\tStandard mode ");
    Serial.println(ens160.setMode(ENS160_OPMODE_STD) ? "done." : "failed!");
  }

  /*********************************/
  /*          AHT2x setup          */
  /*********************************/
  Serial.print("AHT2x initilization...");
  Serial.println(aht2x.begin() ? "done." : "failed!");

  Serial.print("\tNormal mode ");
  Serial.println(aht2x.setNormalMode() ? "done." : "failed!");

  byte status = aht2x.getStatus();

  if (status) {
    Serial.print("\tAHT2x return error code: ");
    Serial.println(status, HEX);

  } else {
    Serial.println("\tAHT2x ready to go!");
  }

  /*********************************/
  /*           WiFi setup          */
  /*********************************/
  WiFi.begin(ssid, password);
  Serial.print("Connecting");

  while(WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("Connected!");
  Serial.print("Connected to: ");
  Serial.println(WiFi.SSID());
  Serial.print("As: ");
  Serial.println(WiFi.getHostname());

}

float aqi =  0;
float tvoc = 0;
float eco2 = 0;
float tempereture = 0;
float humitidy = 0;

char print_buf[100];
String httpRequestData = "";

void loop() {
  // Build the data
  httpRequestData = "Location=home_desktop";

  mhz19b->update_CO2_Tmp();
  pms5003t->update_data();

// Build the data
/*cns is most likley PM100_ATM*/
httpRequestData += "&PM10_STD=1&"\
"PM25_STD=" + String(pms5003t->get_PM25_std()) + "&"\
"PM100_STD=" + String(pms5003t->get_PM100_std()) + "&"\
"PM10_ATM=" + String(pms5003t->get_PM10_atm()) + "&"\
"PM25_ATM=" + String(pms5003t->get_PM25_atm()) + "&"\
"PM100_ATM=" + String(pms5003t->get_cns_atm()) + "&"\
"PART_03=" + String(pms5003t->get_part_03()) + "&"\
"PART_05=" + String(pms5003t->get_part_05()) + "&"\
"PART_10=" + String(pms5003t->get_part_10()) + "&"\
"PART_25=" + String(pms5003t->get_part_25()) + "&"\
"PART_50=" + String(pms5003t->get_part_50()) + "&"\
"PART_100="  + String(pms5003t->get_part_100());

  // Build the data
  httpRequestData += "&MHZ19B-Temperature=" + String(mhz19b->get_Temperature()) + "&"\
                     "MHZ19B-CO2=" + String(mhz19b->get_CO2());

  if (ens160.available()) {
    ens160.set_envdata(tempereture, humitidy); // Should be used to get better results

    ens160.measure(0);

    aqi = ens160.getAQI();
    tvoc = ens160.getTVOC();
    eco2 = ens160.geteCO2();

    // Build the data
    httpRequestData += "&ENS160-CO2=" + String(eco2) + "&"\
                       "ENS160-AQI=" + String(aqi) + "&"\
                       "ENS160-TVOC=" + String(tvoc);
    
  } else {
    Serial.println("ENS160 not avaliable");
  }

  if (aht2x.getStatus() == AHTXX_NO_ERROR) {
    tempereture = aht2x.readTemperature();
    humitidy    = aht2x.readHumidity();

    // Build the data
    httpRequestData += "&AHT2x-Temperature=" + String(tempereture) + "&"\
                       "AHT2x-Humidity=" + humitidy;

  } else {
    Serial.println("AHT2X Error");
  }

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClientSecure* client = new WiFiClientSecure;
    HTTPClient https;

    client->setCACert(root_ca);

    https.begin(*client, hostname);

    /* Build header */
    https.setAuthorization("username", "RguGD5uxZ!PpQCxzkazp^qJvmbz3rE9Qd$#xvZ&&HjsDybW8sLTdjx@9SBhe");
    https.setUserAgent("");
    https.addHeader("Content-Type", "application/x-www-form-urlencoded");
    https.setFollowRedirects(followRedirects_t::HTTPC_STRICT_FOLLOW_REDIRECTS);

    // Send HTTP POST request
    int httpResponseCode = https.POST(httpRequestData);
    String response = https.getString();
    Serial.println(response);

    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
    
    https.end();

    delete(client);
  }

  Serial.print("Head size: ");
  Serial.print(heap_caps_get_free_size(0));
  Serial.println(" Bytes");
  sleep(60);
}