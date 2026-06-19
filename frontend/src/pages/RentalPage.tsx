import { useState } from 'react';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useRentals } from '@/features/rental/useRental';
import { useClients } from '@/features/clients/useClients';
import RentalCartForm from '@/components/rental/RentalCartForm';
import RentalFileForm from '@/components/rental/RentalFileForm';
import './rental.css';

type TabType = 'cart' | 'file';

function RentalPage() {
  const [activeTab, setActiveTab] = useState<TabType>('cart');
  const { data: rentalData, isLoading: rentalsLoading, isError: rentalsError } = useRentals();
  const { data: clientsData, isLoading: clientsLoading } = useClients('');

  if (rentalsLoading || clientsLoading) return <Loading />;

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-file-signature" aria-hidden="true" />
        レンタル手続き
      </div>

      {rentalsError && (
        <Alert variant="danger">
          レンタル情報の読み込みに失敗しました。
        </Alert>
      )}

      <div className="rental-container">
        <div className="rental-tabs">
          <button
            className={`rental-tab ${activeTab === 'cart' ? 'active' : ''}`}
            onClick={() => setActiveTab('cart')}
          >
            カート式
          </button>
          <button
            className={`rental-tab ${activeTab === 'file' ? 'active' : ''}`}
            onClick={() => setActiveTab('file')}
          >
            ファイル式
          </button>
        </div>

        <div className="rental-content">
          {activeTab === 'cart' && (
            <RentalCartForm
              clients={clientsData ?? []}
              rentals={rentalData?.data ?? []}
            />
          )}
          {activeTab === 'file' && (
            <RentalFileForm clients={clientsData ?? []} />
          )}
        </div>
      </div>
    </>
  );
}

export default RentalPage;
