<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Musica;
use Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
// use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AlbumController;

class MusicController extends Controller
{

    private $genero;
    private $artistasc;
    private $albumc;


    public function __construct(){
        $this->artistasc = new ArtistController();
        $this->albumc = new AlbumController();
    }

     // retorna todas as ocurrencias segundo a busca
    public function showbylike($string){
        $musiclike = DB::table('musica')->where('nomemusica', 'LIKE', '%' . $string .'%')->first();
        
        if(!$musiclike){
            return response()->json([
                'message' => 'Nenhum resultado para: ' . $string
            ], 404);
        }
        return response()->json($musiclike);
    }
    
    // retorna apenas o id selecionado
    public function show($id){
        $musica = Musica::find($id);

        if(!$musica){
            return response()->json([
                'message' => 'Música não encontrada'
            ], 404);
        };

        return response()->json($musica);
    }

    // retorna diretorios
    public function retornaMusicaById($id){
        
        $directories = Storage::disk('custom')->allFiles($id);

        return response()->json([
            $directories
        ]);
    }

    // callback atualiza
    public function retornaMusicasArtistaCallBack($id){
        $idartista = $id;

        $musicas = DB::table('musica')->where('artistaID', '=', $idartista)
                        ->select('*')
                        ->get();

        return $musicas;
    }

    // retorna músicas de um id(artista) especifico

    public function retornaMusicasArtista($id){
        $idartista = $id;

        $musicas = DB::table('musica')->where('artistaID', '=', $idartista)
                        ->select('*')
                        ->get();
         

        return response()->json([
            'musicas' => $musicas
        ]);
    }
    // retorna metadados de musicas com mesmo genero da busca
    public function retornaMusicasGeneros($id){
        
        $idmusica = $id;

        $musicageneroid = DB::table('musica_has_genero')->where('musicaID', '=', $id)
                    ->select('generoID')
                    ->first();

        $this->genero = $musicageneroid->generoID;

        
        $musicasdiversas = DB::table('musica_has_genero')->where('generoID', '=', $musicageneroid->generoID)
                        ->select('musicaID')
                        ->get();
        
        $arr = [];
        
        if($musicasdiversas){
            foreach($musicasdiversas as $key => $value){
                $music = DB::table('musica')->where('id', '=', $value->musicaID)->first();
                if($music){
                    array_push($arr, $music);
                }
            }
        }

        return $arr;
    }

    public function retornaArtistasGeneros($id){
        
        $artistagenero = DB::table('artistas_has_generos')->where('generoid', '=', $this->genero)
                    ->select('artistaid')->distinct()->get();

        $artistas = [];

        foreach($artistagenero as $key => $value){
            $art = DB::table('artistas')->where('id', '=', $value->artistaid)->first();
            if($art){
                array_push($artistas, $art);
            }
        }

        if($artistas){
            return $artistas;
        }

        return false;

    }

    // retorna todos os dados
    public function showmetadata($string){
        $artista = DB::table('artistas')->where('nomeartista', 'LIKE', '%' . $string . '%')
        ->select(
            'id',
            'nomeartista',
            'desc_artista',
            'filepath',
            'created_at',
            'updated_at'
            )
            ->distinct()->get();

        // retornar isso para album tambem
                

        $musica = DB::table('musica')->where('nomemusica', 'LIKE', '%' . $string . '%')
        ->select(
            'id',
            'nomemusica',
            'filepath',
            'filepath_avatar',
            'created_at',
            'updated_at'
        )
        ->distinct()->get();

        $album = DB::table('album')->where('titulo_album', 'LIKE', '%' . $string . '%')
        ->select(
            'id',
            'titulo_album',
            'desc_album',
            'filepath_avatar',
            'created_at',
            'updated_at'
        )
        ->distinct()->get();

        if(!$musica && !$album && !$artista){
            return response()->json([
                'message' => 'Nenhum resultado para: ' . $string
            ]);
        };

        $vals = [
            'musica' => $musica,
            'artista' => $artista,
            'album' => $album
        ];

        foreach($vals as $key => $value){
            if(count($value) < 1){
                unset($vals[$key]);
            }
        }

        foreach($vals as $key => $value){
            if($key == 'musica'){
                $id = $musica[0]->id;
                $album = DB::table('album_has_musica')
                ->join('album', 'album_has_musica.albumID', '=', 'album.id')->where('album_has_musica.musicaID', '=', $id)
                ->select(
                    'album.id',
                    'album.titulo_album',
                    'album.desc_album',
                    'filepath_avatar',
                    'created_at',
                    'updated_at'
                    )->distinct()->get();

                $artista = DB::table('artistas_has_musicas')
                            ->join('artistas', 'artistas_has_musicas.artistaID',  '=', 'artistas.id')->where('artistas_has_musicas.musicaID', '=', $id)
                            ->select(
                                'artistas.id',
                                'artistas.nomeartista',
                                'artistas.desc_artista',
                                'filepath',
                                'created_at',
                                'updated_at'
                            )->distinct()->get();

                if(!$artista && !$album){
                    return response()->json([
                        'message' => 'Nenhum dado encontrado para: ' . $string
                    ], 404);
                }

                $opcoesmusicas = self::retornaMusicasGeneros($id);
                $opcoesartistas = self::retornaArtistasGeneros($id);


                return response()->json(
                    [
                    'find' => [
                        'music' => [ 
                            'musica' => $musica,
                            'album' => $album,
                            'artista' => $artista
                        ]
                    ],
                    'metadados' => [
                        'musicasgeneros' => $opcoesmusicas,
                        'artistasgeneros' => $opcoesartistas
                    ]
                ]);
            }elseif($key == 'artista'){
                return response()->json(
                    $this->artistasc->showmetadata($string)
                );
            }else{
                return response()->json(
                    $this->albumc->showbylike($string)
                );
            }
        }        
    }
    // metodo all
    public function index(){
        $musica = Musica::all();
        return response()->json($musica);
    }

    public function buscaByGenero($data){
        $genero = $data;

        
        $generos = DB::table('generos')->select('id')->where('desc_genero', 'LIKE', '%'.$genero.'%')->get();
        
        // dd($generos);

        $resultgenero = $generos[0]->id;

        $musicasgenero = DB::table('musica_has_genero')->select('musicaID')->where('generoID', '=', $resultgenero)->get();

        $arr = [];

        foreach($musicasgenero as $key => $value){
            array_push($arr, $value->musicaID);
        }

        $select = DB::table('musica')->whereIn('id', $arr)->get();

        return response()->json([
            'result' => $select
        ]);
    }

    public function atualizaMusicaById(Request $request){

        $id = $request->input('id_user');

        $newname = $request->input('newname');

        $oldname = $request->input('oldname');

        $nname = $newname . '.mp3';

        $idmusica = DB::table('musica')->select('id')->where('nomemusica' , 'LIKE', '%' . $oldname . '%')->get();

        $idm = $idmusica[0]->id;

        $update = DB::table('musica')->where('id', $idm)->update(
            [
                'nomemusica' => $nname
            ]
        );
        
        // ver com o bruno sobre esse move
        // Storage::move(storage_path('app/sdg/audio/' . (string)$id . '/' . $oldname), storage_path('app/sdg/audio/' . (string)$id . '/' . $nname));

        if(!$update){
            return response()->json([
                'erro'
            ]);
        };

        $musicas = self::retornaMusicasArtistaCallBack($id);
        
        return response()->json([
            'musicas' => $musicas
        ]);
    }

    // metodo upload musicas
    public function uploadMusicas(Request $request){
        // get audio da request
        $id = $request->input('id_user');
        // get id da request
        $sound = $request->file('audio');

        $image = $request->file('image');

        $filename = $sound->getClientOriginalName();

        $filenameimage = $image->getClientOriginalName();
        
        $location = storage_path('app/sdg/audio/' . (string)$id . '/');

        $sound->move($location,$filename);
        
        $locationimage = storage_path('app/sdg/audio/' . (string)$id . '/images' .'/');
        
        // $directories = Storage::disk('custom')->makeDirectory( (string)$id . '/images');
        
        $image->move($locationimage, $filenameimage);
        
        // insert
        DB::table('musica')->insert(
            [
                'albumID' => 0,
                'artistaID'=> $id,
                'filepath' => 'audios/'.(string)$id . '/' . $filename,
                'filepath_avatar' => 'audios/' .(string)$id . '/images' . '/' . $filenameimage,
                'created_at' => date('d/m/y h:i:s a', time()),
                'updated_at' => '',
                'deleted_at' => '',
                'nomemusica' => $filename
            ]
        );

        // OCC - PESQUISAR

        $idmusicaartista = DB::table('musica')->select('id')->where('artistaID', '=', $id)->orderByRaw('created_at DESC')->get();

        DB::table('artistas_has_musicas')->insert(
            [
                'artistaID' => 2, // (int)$request->input('id_user')
                'musicaID' => $idmusicaartista[0]->id
            ]
        );

        DB::table('musica_has_genero')->insert(
            [
                'musicaID' => $idmusicaartista[0]->id,
                'generoID' => 1
            ]
        );

        return response()->json([
            'response' => 'OK'
        ]);
    }

    // remove musicas pelo id do usuario
    public function removeMusicaById(Request $request){

        $dados = $request->only('idmusica', 'usuario', 'idusuario');

        // fazer select no banco para encontrar nome da musica

        $nmusica = DB::table('musica')->select('nomemusica')->where('id', '=', $dados['idmusica'])->get();

        if(!$nmusica){
            return response()->json([
                'error' => 'música não localizada, erro!'
            ]);
        }

        DB::table('musica_has_genero')->where('musicaID', '=', $dados['idmusica'])->delete();

        DB::table('artistas_has_musicas')->where('musicaID', '=', $dados['idmusica'])->delete();

        DB::table('musica')->where('id', '=', $dados['idmusica'])->delete();

        $musicas = DB::table('musica')->where('artistaID', '=', $dados['idusuario'])
                        ->select('*')
                        ->get();

        return response()->json([
            'musicas' => $musicas
        ]);
    }
}
