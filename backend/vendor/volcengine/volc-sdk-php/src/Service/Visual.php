<?php

namespace Volc\Service;

use Volc\Base\V4Curl;

class Visual extends V4Curl
{
    protected function getConfig(string $region = '')
    {
        return [
            'host' => 'https://visual.volcengineapi.com',
            'config' => [
                'timeout' => 30.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => 'cn-north-1',
                    'service' => 'cv',
                ],
            ],
        ];
    }

    public function JPCartoon(array $query = [])
    {
        $response = $this->request('JPCartoon', $query);
        return $response->getBody();
    }

    public function FaceSwap(array $query = [])
    {
        $response = $this->request('FaceSwap', $query);
        return $response->getBody();
    }

    public function HumanSegment(array $query = [])
    {
        $response = $this->request('HumanSegment', $query);
        return $response->getBody();
    }

    public function GeneralSegment(array $query = [])
    {
        $response = $this->request('GeneralSegment', $query);
        return $response->getBody();
    }

    public function EnhancePhoto(array $query = [])
    {
        $response = $this->request('EnhancePhoto', $query);
        return $response->getBody();
    }

    public function ConvertPhoto(array $query = [])
    {
        $response = $this->request('ConvertPhoto', $query);
        return $response->getBody();
    }

    public function TGameCartoon(array $query = [])
    {
        $response = $this->request('TGameCartoon', $query);
        return $response->getBody();
    }

    public function AllAgeGeneration(array $query = [])
    {
        $response = $this->request('AllAgeGeneration', $query);
        return $response->getBody();
    }

    public function FaceFusionMovieSubmitTask(array $query = [])
    {
        $response = $this->request('FaceFusionMovieSubmitTask', $query);
        return $response->getBody();
    }

    public function FaceFusionMovieGetResult(array $query = [])
    {
        $response = $this->request('FaceFusionMovieGetResult', $query);
        return $response->getBody();
    }

    public function FaceFusionMovieSync(array $query = [])
    {
        $response = $this->request('FaceFusionMovieSync', $query);
        return $response->getBody();
    }

    public function T2ILDM(array $query = [])
    {
        $response = $this->request('T2ILDM', $query);
        return $response->getBody();
    }

    public function Img2ImgStyle(array $query = [])
    {
        $response = $this->request('Img2ImgStyle', $query);
        return $response->getBody();
    }

    public function Img2ImgAnime(array $query = [])
    {
        $response = $this->request('Img2ImgAnime', $query);
        return $response->getBody();
    }

    public function CertToken(array $query = [])
    {
        $response = $this->request('CertToken', $query);
        return $response->getBody();
    }

    public function CertVerify(array $query = [])
    {
        $response = $this->request('CertVerify', $query);
        return $response->getBody();
    }

    public function CertVerifyQuery(array $query = [])
    {
        $response = $this->request('CertVerifyQuery', $query);
        return $response->getBody();
    }

    public function CertConfigInit(array $query = [])
    {
        $response = $this->request('CertConfigInit', $query);
        return $response->getBody();
    }

    public function OCRNormal(array $query = [])
    {
        $response = $this->request('OCRNormal', $query);
        return $response->getBody();
    }

    public function IDCard(array $query = [])
    {
        $response = $this->request('IDCard', $query);
        return $response->getBody();
    }

    public function BankCard(array $query = [])
    {
        $response = $this->request('BankCard', $query);
        return $response->getBody();
    }

    public function FacePretty(array $query = [])
    {
        $response = $this->request('FacePretty', $query);
        return $response->getBody();
    }

    public function PotraitEffect(array $query = [])
    {
        $response = $this->request('PotraitEffect', $query);
        return $response->getBody();
    }

    public function SetAPI($action, $version)
    {
        $addApi = [
            $action => [
                'url' => '/',
                'method' => 'post',
                'config' => [
                    'query' => [
                        'Action' => $action,
                        'Version' => $version,
                    ],
                ],
            ],
        ];
        $this->apiList = array_merge($this->apiList, $addApi);
    }

    public function CallAPI($action, array $query = [])
    {
        $response = $this->request($action, $query);
        return $response->getBody();
    }

    public function HairSegment(array $query = [])
    {
        $response = $this->request('HairSegment', $query);
        return $response->getBody();
    }

    public function DollyZoom(array $query = [])
    {
        $response = $this->request('DollyZoom', $query);
        return $response->getBody();
    }

    public function Img2Video3D(array $query = [])
    {
        $response = $this->request('Img2Video3D', $query);
        return $response->getBody();
    }
    public function HairStyle(array $query = [])
    {
        $response = $this->request('HairStyle', $query);
        return $response->getBody();
    }
    public function HairStyleV2(array $query = [])
    {
        $response = $this->request('HairStyleV2', $query);
        return $response->getBody();
    }
    public function ImageAnimation(array $query = [])
    {
        $response = $this->request('ImageAnimation', $query);
        return $response->getBody();
    }
    public function EmoticonEdit(array $query = [])
    {
        $response = $this->request('EmoticonEdit', $query);
        return $response->getBody();
    }
    public function EmotionPortrait(array $query = [])
    {
        $response = $this->request('EmotionPortrait', $query);
        return $response->getBody();
    }
    public function EyeClose2Open(array $query = [])
    {
        $response = $this->request('EyeClose2Open', $query);
        return $response->getBody();
    }
    public function TupoCartoon(array $query = [])
    {
        $response = $this->request('TupoCartoon', $query);
        return $response->getBody();
    }
    public function DistortionFree(array $query = [])
    {
        $response = $this->request('DistortionFree', $query);
        return $response->getBody();
    }
    public function BodyDetection(array $query = [])
    {
        $response = $this->request('BodyDetection', $query);
        return $response->getBody();
    }
    public function ImageFlow(array $query = [])
    {
        $response = $this->request('ImageFlow', $query);
        return $response->getBody();
    }
    public function ImageScoreV2(array $query = [])
    {
        $response = $this->request('ImageScoreV2', $query);
        return $response->getBody();
    }
    public function CarPlateDetection(array $query = [])
    {
        $response = $this->request('CarPlateDetection', $query);
        return $response->getBody();
    }
    public function CarDetection(array $query = [])
    {
        $response = $this->request('CarDetection', $query);
        return $response->getBody();
    }
    public function EntityDetect(array $query = [])
    {
        $response = $this->request('EntityDetect', $query);
        return $response->getBody();
    }
    public function GoodsDetect(array $query = [])
    {
        $response = $this->request('GoodsDetect', $query);
        return $response->getBody();
    }
    public function PoemMaterial(array $query = [])
    {
        $response = $this->request('PoemMaterial', $query);
        return $response->getBody();
    }
    public function CarSegment(array $query = [])
    {
        $response = $this->request('CarSegment', $query);
        return $response->getBody();
    }
    public function SkySegment(array $query = [])
    {
        $response = $this->request('SkySegment', $query);
        return $response->getBody();
    }
    public function GoodsSegment(array $query = [])
    {
        $response = $this->request('GoodsSegment', $query);
        return $response->getBody();
    }
    public function ImageCorrection(array $query = [])
    {
        $response = $this->request('ImageCorrection', $query);
        return $response->getBody();
    }
    public function ImageStyleConversion(array $query = [])
    {
        $response = $this->request('ImageStyleConversion', $query);
        return $response->getBody();
    }
    public function ImageCut(array $query = [])
    {
        $response = $this->request('ImageCut', $query);
        return $response->getBody();
    }
    public function StretchRecovery(array $query = [])
    {
        $response = $this->request('StretchRecovery', $query);
        return $response->getBody();
    }
    public function EnhancePhotoV2(array $query = [])
    {
        $response = $this->request('EnhancePhotoV2', $query);
        return $response->getBody();
    }
    public function ConvertPhotoV2(array $query = [])
    {
        $response = $this->request('ConvertPhotoV2', $query);
        return $response->getBody();
    }
    public function OverResolutionV2(array $query = [])
    {
        $response = $this->request('OverResolutionV2', $query);
        return $response->getBody();
    }
    public function FaceSwapV2(array $query = [])
    {
        $response = $this->request('FaceSwapV2', $query);
        return $response->getBody();
    }
    public function FaceswapAI(array $query = [])
    {
        $response = $this->request('FaceswapAI', $query);
        return $response->getBody();
    }
    public function CertH5ConfigInit(array $query = [])
    {
        $response = $this->request('CertH5ConfigInit', $query);
        return $response->getBody();
    }
    public function CertH5Token(array $query = [])
    {
        $response = $this->request('CertH5Token', $query);
        return $response->getBody();
    }
    public function HighAesSmartDrawing(array $query = [])
    {
        $response = $this->request('HighAesSmartDrawing', $query);
        return $response->getBody();
    }
    public function Img2ImgInpainting(array $query = [])
    {
        $response = $this->request('Img2ImgInpainting', $query);
        return $response->getBody();
    }
    public function Img2ImgInpaintingEdit(array $query = [])
    {
        $response = $this->request('Img2ImgInpaintingEdit', $query);
        return $response->getBody();
    }
    public function Img2ImgOutpainting(array $query = [])
    {
        $response = $this->request('Img2ImgOutpainting', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateDisneyStyleNoFace(array $query = [])
    {
        $response = $this->request('Img2ImgCreateDisneyStyleNoFace', $query);
        return $response->getBody();
    }
    public function Img2ImgCreatePastelBoys2D(array $query = [])
    {
        $response = $this->request('Img2ImgCreatePastelBoys2D', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateEtherRealMix(array $query = [])
    {
        $response = $this->request('Img2ImgCreateEtherRealMix', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateToonyou(array $query = [])
    {
        $response = $this->request('Img2ImgCreateToonyou', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateAnyloraMakoto(array $query = [])
    {
        $response = $this->request('Img2ImgCreateAnyloraMakoto', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateRevAnimated(array $query = [])
    {
        $response = $this->request('Img2ImgCreateRevAnimated', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateAesBlueline(array $query = [])
    {
        $response = $this->request('Img2ImgCreateAesBlueline', $query);
        return $response->getBody();
    }
    public function Img2ImgCreateInkAndWater(array $query = [])
    {
        $response = $this->request('Img2ImgCreateInkAndWater', $query);
        return $response->getBody();
    }
    public function Img2ImgWaterColorStyle(array $query = [])
    {
        $response = $this->request('Img2ImgWaterColorStyle', $query);
        return $response->getBody();
    }
    public function EntitySegment(array $query = [])
    {
        $response = $this->request('EntitySegment', $query);
        return $response->getBody();
    }
    public function Img2ImgAnimeAcceleratedMaintainID(array $query = [])
    {
        $response = $this->request('Img2ImgAnimeAcceleratedMaintainID', $query);
        return $response->getBody();
    }
    public function Img2ImgXLSft(array $query = [])
    {
        $response = $this->request('Img2ImgXLSft', $query);
        return $response->getBody();
    }
    public function Text2ImgXLSft(array $query = [])
    {
        $response = $this->request('Text2ImgXLSft', $query);
        return $response->getBody();
    }
    public function Img2ImgComicsStyle(array $query = [])
    {
        $response = $this->request('Img2ImgComicsStyle', $query);
        return $response->getBody();
    }
    public function Img2ImgExquisiteStyle(array $query = [])
    {
        $response = $this->request('Img2ImgExquisiteStyle', $query);
        return $response->getBody();
    }
    public function FaceCompare(array $query = [])
    {
        $response = $this->request('FaceCompare', $query);
        return $response->getBody();
    }

    public function StillLivenessImg(array $query = [])
    {
        $response = $this->request('StillLivenessImg', $query);
        return $response->getBody();
    }
    public function CertSrcFaceComp(array $query = [])
    {
        $response = $this->request('CertSrcFaceComp', $query);
        return $response->getBody();
    }
    public function CVProcess(array $query = [])
    {
        $response = $this->request('CVProcess', $query);
        return $response->getBody();
    }
    public function CVSubmitTask(array $query = [])
    {
        $response = $this->request('CVSubmitTask', $query);
        return $response->getBody();
    }
    public function CVGetResult(array $query = [])
    {
        $response = $this->request('CVGetResult', $query);
        return $response->getBody();
    }
    public function CVSync2AsyncSubmitTask(array $query = [])
    {
        $response = $this->request('CVSync2AsyncSubmitTask', $query);
        return $response->getBody();
    }
    public function CVSync2AsyncGetResult(array $query = [])
    {
        $response = $this->request('CVSync2AsyncGetResult', $query);
        return $response->getBody();
    }
    public function CertLivenessVerifyQuery(array $query = [])
    {
        $response = $this->request('CertLivenessVerifyQuery', $query);
        return $response->getBody();
    }

    protected $apiList = [
        'CVProcess' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CVProcess',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CertLivenessVerifyQuery' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertLivenessVerifyQuery',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CVSubmitTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CVSubmitTask',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CVGetResult' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CVGetResult',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CVSync2AsyncSubmitTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CVSync2AsyncSubmitTask',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CVSync2AsyncGetResult' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CVSync2AsyncGetResult',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'JPCartoon' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'JPCartoon',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'CertSrcFaceComp' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertSrcFaceComp',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'StillLivenessImg' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'StillLivenessImg',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'FaceCompare' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceCompare',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgComicsStyle' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgComicsStyle',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgExquisiteStyle' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgExquisiteStyle',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Text2ImgXLSft' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Text2ImgXLSft',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgXLSft' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgXLSft',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgAnimeAcceleratedMaintainID' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgAnimeAcceleratedMaintainID',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'EntitySegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EntitySegment',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgWaterColorStyle' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgWaterColorStyle',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateInkAndWater' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateInkAndWater',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateAesBlueline' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateAesBlueline',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateRevAnimated' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateRevAnimated',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateEtherRealMix' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateEtherRealMix',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateAnyloraMakoto' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateAnyloraMakoto',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateToonyou' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateToonyou',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreatePastelBoys2D' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreatePastelBoys2D',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgCreateDisneyStyleNoFace' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgCreateDisneyStyleNoFace',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgOutpainting' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgOutpainting',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgInpainting' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgInpainting',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2ImgInpaintingEdit' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgInpaintingEdit',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'HighAesSmartDrawing' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'HighAesSmartDrawing',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'FaceSwapV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceSwap',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CertH5ConfigInit' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertH5ConfigInit',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'CertH5Token' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertH5Token',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => '2022-08-31',
                ]
            ],
        ],
        'FaceswapAI' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceswapAI',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'OverResolutionV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OverResolutionV2',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'ConvertPhotoV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ConvertPhotoV2',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'EnhancePhotoV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EnhancePhotoV2',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'StretchRecovery' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'StretchRecovery',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'ImageCut' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ImageCut',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'ImageStyleConversion' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ImageStyleConversion',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'ImageCorrection' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ImageCorrection',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'GoodsSegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GoodsSegment',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'SkySegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SkySegment',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'CarSegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CarSegment',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'PoemMaterial' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'PoemMaterial',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'GoodsDetect' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GoodsDetect',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'EntityDetect' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EntityDetect',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'CarDetection' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CarDetection',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'CarPlateDetection' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CarPlateDetection',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'ImageScoreV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ImageScoreV2',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'ImageFlow' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ImageFlow',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'BodyDetection' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BodyDetection',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'DistortionFree' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DistortionFree',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'TupoCartoon' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'TupoCartoon',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'EyeClose2Open' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EyeClose2Open',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'EmotionPortrait' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EmotionPortrait',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'ImageAnimation' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ImageAnimation',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'EmoticonEdit' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EmoticonEdit',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'HairStyle' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'HairStyle',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'HairStyleV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'HairStyle',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'Img2Video3D' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2Video3D',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ],
        ],
        'HairSegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'HairSegment',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'DollyZoom' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DollyZoom',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'FaceSwap' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceSwap',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'HumanSegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'HumanSegment',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'GeneralSegment' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GeneralSegment',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'EnhancePhoto' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EnhancePhoto',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'ConvertPhoto' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ConvertPhoto',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'TGameCartoon' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => '3DGameCartoon',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'AllAgeGeneration' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'AllAgeGeneration',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'FaceFusionMovieSubmitTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceFusionMovieSubmitTask',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'FaceFusionMovieGetResult' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceFusionMovieGetResult',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'FaceFusionMovieSync' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FaceFusionMovie',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'T2ILDM' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'T2ILDM',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'Img2ImgStyle' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgStyle',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'Img2ImgAnime' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Img2ImgAnime',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'CertToken' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertToken',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'CertVerify' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertVerify',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'CertVerifyQuery' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertVerifyQuery',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
        'CertConfigInit' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CertConfigInit',
                    'Version' => '2022-08-31',
                ],
                'header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ],

        'OCRNormal' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OCRNormal',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'IDCard' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'IDCard',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'BankCard' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BankCard',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'FacePretty' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FacePretty',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
        'PotraitEffect' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'PotraitEffect',
                    'Version' => '2020-08-26',
                ],
            ],
        ],
    ];
}
