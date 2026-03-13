/**
 * Vehicle lookup via Check Car Details API (checkcardetails.co.uk)
 * Sign up at https://api.checkcardetails.co.uk/auth/register for an API key
 */
const express = require('express');
const router = express.Router();

const CHECK_CAR_DETAILS_BASE = process.env.CHECK_CAR_DETAILS_BASE || 'https://api.checkcardetails.co.uk';
const API_KEY = process.env.CHECK_CAR_DETAILS_API_KEY;
const UKVD_API_KEY = process.env.UKVD_API_KEY || API_KEY; // Fallback to CCD key (some resellers use same backend)
const MOCK_MODE = process.env.CHECK_CAR_DETAILS_MOCK === '1' || process.env.CHECK_CAR_DETAILS_MOCK === 'true';

router.get('/lookup', async (req, res) => {
  const vrm = (req.query.vrm || '').replace(/\s/g, '').toUpperCase();
  if (!vrm) {
    return res.status(400).json({ error: 'VRM (registration) required' });
  }

  if (!MOCK_MODE && !API_KEY) {
    return res.status(503).json({
      error: 'Vehicle API not configured',
      hint: 'Set CHECK_CAR_DETAILS_API_KEY in .env. Sign up at api.checkcardetails.co.uk/auth/register'
    });
  }

  try {
    if (MOCK_MODE) {
      const mock = {
        VehicleRegistration: {
          DateOfLastUpdate: '2023-02-10T14:42:24',
          Colour: 'BLACK',
          VehicleClass: 'Car',
          CertificateOfDestructionIssued: false,
          EngineNumber: 'A448H555',
          EngineCapacity: '1598',
          TransmissionCode: 'A',
          Exported: false,
          YearOfManufacture: '2007',
          WheelPlan: '2 AXLE RIGID BODY',
          DateExported: null,
          Scrapped: false,
          Transmission: 'AUTO 6 GEARS',
          DateFirstRegisteredUk: '2007-05-14T00:00:00',
          Model: 'COOPER S AUTO',
          GearCount: 6,
          ImportNonEu: false,
          PreviousVrmGb: null,
          GrossWeight: 1605,
          DoorPlanLiteral: '3 DOOR HATCHBACK',
          MvrisModelCode: 'ACN',
          Vin: 'WMWMF72060TL55555',
          Vrm: vrm,
          DateFirstRegistered: '2007-05-14T00:00:00',
          DateScrapped: null,
          DoorPlan: '13',
          YearMonthFirstRegistered: '2007-05',
          VinLast5: '39282',
          VehicleUsedBeforeFirstRegistration: false,
          MaxPermissibleMass: 1605,
          Make: 'MINI',
          MakeModel: 'MINI COOPER S AUTO',
          TransmissionType: 'Automatic',
          SeatingCapacity: 4,
          FuelType: 'PETROL',
          Co2Emissions: 182,
          Imported: false,
          MvrisMakeCode: 'C1',
          PreviousVrmNi: null,
          VinConfirmationFlag: null
        },
        Dimensions: {
          UnladenWeight: 1155,
          RigidArtic: 'RIGID',
          BodyShape: 'NA',
          PayloadVolume: null,
          PayloadWeight: null,
          Height: 1407,
          NumberOfDoors: 3,
          NumberOfSeats: 4,
          KerbWeight: 1155,
          GrossTrainWeight: null,
          FuelTankCapacity: 50,
          LoadLength: null,
          DataVersionNumber: null,
          WheelBase: 2467,
          CarLength: 3714,
          Width: 1683,
          NumberOfAxles: 2,
          GrossVehicleWeight: 1605,
          GrossCombinedWeight: null
        },
        Engine: {
          FuelCatalyst: 'C',
          Stroke: 86,
          PrimaryFuelFlag: 'Y',
          ValvesPerCylinder: 4,
          Aspiration: 'Turbocharged',
          FuelSystem: 'P MPI Nat Asp Cat Euro 4',
          NumberOfCylinders: 4,
          CylinderArrangement: 'I',
          ValveGear: 'DOHC',
          Location: 'FRONT',
          Description: null,
          Bore: 77,
          Make: 'BMW',
          FuelDelivery: 'Multi-Point Injection'
        },
        Performance: {
          Torque: { FtLb: 177.1, Nm: 240, Rpm: 1600 },
          NoiseLevel: null,
          DataVersionNumber: null,
          Power: { Bhp: 171.6, Rpm: 5500, Kw: 128 },
          MaxSpeed: { Kph: 220, Mph: 137 },
          Co2: 182,
          Particles: null,
          Acceleration: { Mph: 6.8, Kph: 7.2, ZeroTo60Mph: 6.8, ZeroTo100Kph: 7.2 }
        },
        Consumption: {
          ExtraUrban: { Lkm: 5.7, Mpg: 49.6 },
          UrbanCold: { Lkm: 10.9, Mpg: 25.9 },
          Combined: { Lkm: 7.6, Mpg: 37.2 }
        },
        VehicleHistory: {
          V5CCertificateCount: 1,
          PlateChangeCount: 1,
          NumberOfPreviousKeepers: 0,
          V5CCertificateList: [{ CertificateDate: '2012-06-07T00:00:00' }],
          KeeperChangesCount: 0,
          VicCount: 0,
          ColourChangeCount: null,
          ColourChangeList: null,
          KeeperChangesList: null,
          PlateChangeList: [{
            CurrentVRM: 'A1',
            TransferType: 'DataMove',
            DateOfReceipt: '2007-05-17T00:00:00',
            PreviousVRM: 'LC07PBF',
            DateOfTransaction: '2007-05-17T00:00:00'
          }],
          VicList: null,
          ColourChangeDetails: {
            CurrentColour: 'BLACK',
            NumberOfPreviousColours: 0,
            OriginalColour: null,
            LastColour: null,
            DateOfLastColourChange: null
          }
        },
        SmmtDetails: {
          Range: 'HATCH',
          FuelType: 'PETROL',
          EngineCapacity: '1598',
          MarketSectorCode: 'AA',
          CountryOfOrigin: 'UNITED KINGDOM',
          ModelCode: '038',
          ModelVariant: 'COOPER S AUTO',
          DataVersionNumber: null,
          NumberOfGears: 6,
          NominalEngineCapacity: 1.6,
          MarqueCode: 'BB',
          Transmission: 'AUTOMATIC',
          BodyStyle: 'Hatchback',
          VisibilityDate: '01/03/2007',
          SysSetupDate: '01/03/2007',
          Marque: 'MINI',
          CabType: 'NA',
          TerminateDate: '22/12/2010',
          Series: 'R56',
          NumberOfDoors: 3,
          DriveType: '4X2'
        },
        vedRate: {
          Standard: { SixMonth: 176, TwelveMonth: 320 },
          VedCo2Emissions: 182,
          vedBand: 'I',
          VedCo2Band: 'I'
        },
        General: {
          PowerDelivery: 'NORMAL',
          TypeApprovalCategory: 'M1',
          SeriesDescription: 'MK2',
          DriverPosition: 'R',
          DrivingAxle: 'FWD',
          DataVersionNumber: null,
          EuroStatus: '4',
          IsLimitedEdition: false
        }
      };
      Object.assign(mock, { _fromMock: true });
      return res.json(mock);
    }
    // Check Car Details correct format: /vehicledata/vehicleregistration?apikey=&vrm=
    const UKVD_KEY = process.env.UKVD_API_KEY || API_KEY;
    const attempts = [
      { url: `${CHECK_CAR_DETAILS_BASE}/vehicledata/vehicleregistration?apikey=${encodeURIComponent(API_KEY)}&vrm=${encodeURIComponent(vrm)}`, auth: 'none' },
      // UK Vehicle Data (if UKVD_API_KEY set)
      { url: `https://uk1.ukvehicledata.co.uk/api/datapackage/VehicleRegistration?v=2&api_nullitems=1&auth_apikey=${encodeURIComponent(UKVD_KEY)}&key_VRM=${encodeURIComponent(vrm)}`, auth: 'none' },
      { url: `${CHECK_CAR_DETAILS_BASE}/api/vehicle?vrm=${encodeURIComponent(vrm)}&apikey=${encodeURIComponent(API_KEY)}`, auth: 'none' },
      { url: `${CHECK_CAR_DETAILS_BASE}/api/vehicle?registrationNumber=${encodeURIComponent(vrm)}&apikey=${encodeURIComponent(API_KEY)}`, auth: 'none' },
    ];

    let r, text;
    for (const a of attempts) {
      const opts = { headers: { 'Accept': 'application/json' } };
      if (a.auth === 'bearer') opts.headers['Authorization'] = `Bearer ${API_KEY}`;
      r = await fetch(a.url, opts);
      text = await r.text();
      if (r.ok) break;
      const safeUrl = a.url.replace(API_KEY, '***').replace(UKVD_KEY, '***');
      console.log('[Vehicle API]', r.status, safeUrl.slice(0, 100) + '…', text.slice(0, 80));
    }

    if (!r.ok) {
      let detail = r.status === 404 ? 'Registration not found' : text;
      try {
        const err = JSON.parse(text);
        if (err.message) detail = err.message;
        else if (err.error) detail = err.error;
      } catch (_) {}
      return res.status(r.status).json({
        error: 'Vehicle lookup failed',
        detail: (detail || 'Unknown error').substring(0, 200),
        hint: 'Check Car Details does not publish their endpoint. Consider UK Vehicle Data (ukvehicledata.co.uk) — add UKVD_API_KEY to .env for a documented API.'
      });
    }

    const data = text ? JSON.parse(text) : {};

    // UK Vehicle Data wraps data in Response + DataItems
    const unwrapped = data.DataItems || data;
    if (data.Response?.StatusCode && data.Response.StatusCode !== 'Success') {
      return res.status(404).json({
        error: 'Vehicle lookup failed',
        detail: data.Response.StatusMessage || 'Registration not found'
      });
    }

    // Normalise to full structure if API returns different format
    const normalised = normaliseVehicleResponse(unwrapped, vrm);
    return res.json(normalised);
  } catch (err) {
    console.error('Vehicle lookup error:', err);
    return res.status(500).json({ error: 'Vehicle lookup failed', detail: err.message });
  }
});

function normaliseVehicleResponse(raw, vrm) {
  if (!raw) return { error: 'No data' };

  // If already has VehicleRegistration, return as-is
  if (raw.VehicleRegistration || raw.Vehicle?.VehicleRegistration) {
    return raw;
  }

  // Map Check Car Details basic response to our expected structure
  const vr = raw.registrationNumber || raw.VehicleRegistration;
  return {
    VehicleRegistration: {
      Vrm: raw.registrationNumber || vrm,
      Make: raw.make,
      Model: raw.model,
      Colour: raw.colour,
      FuelType: raw.fuelType,
      EngineCapacity: String(raw.engineCapacity || ''),
      YearOfManufacture: raw.yearOfManufacture,
      Transmission: raw.transmission,
      Co2Emissions: raw.co2Emissions,
      DateFirstRegistered: raw.dateFirstRegistered,
      DoorPlanLiteral: raw.doorPlan,
      ...raw
    },
    mot: raw.mot || {},
    Dimensions: raw.Dimensions || {},
    Engine: raw.Engine || {},
    Performance: raw.Performance || {},
    Consumption: raw.Consumption || {},
    VehicleHistory: raw.VehicleHistory || {},
    SmmtDetails: raw.SmmtDetails || {},
    vedRate: raw.vedRate || {},
    General: raw.General || {},
    mot: raw.mot || {}
  };
}

module.exports = router;
