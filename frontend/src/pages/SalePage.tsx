import { useState } from 'react';
import Alert from '@/components/ui/Alert';
import Loading from '@/components/ui/Loading';
import { useSales } from '@/features/sale/useSale';
import { useClients } from '@/features/clients/useClients';
import SaleCartForm from '@/components/sale/SaleCartForm';
import SaleFileForm from '@/components/sale/SaleFileForm';
import './sale.css';

type TabType = 'cart' | 'file';

function SalePage() {
  const [activeTab, setActiveTab] = useState<TabType>('cart');
  const { data: saleData, isLoading: salesLoading, isError: salesError } = useSales();
  const { data: clientsData, isLoading: clientsLoading } = useClients('');

  if (salesLoading || clientsLoading) return <Loading />;

  return (
    <>
      <div className="page-bar">
        <i className="fa fa-file-signature" aria-hidden="true" />
        販売手続き
      </div>

      {salesError && (
        <Alert variant="danger">
          販売情報の読み込みに失敗しました。
        </Alert>
      )}

      <div className="sale-container">
        <div className="sale-tabs">
          <button
            className={`sale-tab ${activeTab === 'cart' ? 'active' : ''}`}
            onClick={() => setActiveTab('cart')}
          >
            カート式
          </button>
          <button
            className={`sale-tab ${activeTab === 'file' ? 'active' : ''}`}
            onClick={() => setActiveTab('file')}
          >
            ファイル式
          </button>
        </div>

        <div className="sale-content">
          {activeTab === 'cart' && (
            <SaleCartForm
              clients={clientsData ?? []}
              sales={saleData?.data ?? []}
            />
          )}
          {activeTab === 'file' && (
            <SaleFileForm clients={clientsData ?? []} />
          )}
        </div>
      </div>
    </>
  );
}

export default SalePage;
